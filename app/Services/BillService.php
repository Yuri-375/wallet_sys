<?php

namespace App\Services;

use App\Models\BillPayment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class BillService
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    // Create and process a bill payment.
    public function pay(
        User $user,
        int $billerId,
        string $accountNumber,
        int $amount,
        ?string $idempotencyKey = null
    ): BillPayment {
        $this->validatePayment(
            user: $user,
            billerId: $billerId,
            accountNumber: $accountNumber,
            amount: $amount,
            idempotencyKey: $idempotencyKey
        );

        $existing = BillPayment::where(
            'idempotency_key',
            $idempotencyKey
        )->first();

        if ($existing) {
            return $existing;
        }

        $payment = $this->createPendingPayment(
            user: $user,
            billerId: $billerId,
            accountNumber: $accountNumber,
            amount: $amount,
            idempotencyKey: $idempotencyKey
        );

        try {
            $result = $this->callMockBillProvider(
                billerId: $billerId,
                accountNumber: $accountNumber,
                amount: $amount
            );

            if ($result['success']) {
                return $this->completePayment(
                    $payment,
                    $result['external_reference']
                );
            }

            return $this->failPayment(
                $payment,
                $result['message'] ?? 'Bill provider rejected payment.'
            );
        } catch (Throwable $e) {
            $this->markProviderUncertain($payment);

            throw $e;
        }
    }

    // Validate bill payment input.
    private function validatePayment(
        User $user,
        int $billerId,
        string $accountNumber,
        int $amount,
        ?string $idempotencyKey
    ): void {
        $errors = [];

        if ($amount <= 0) {
            $errors['amount'] = 'Bill amount must be greater than zero.';
        }

        if (empty($accountNumber)) {
            $errors['account_number'] = 'Account number is required.';
        }

        if (empty($billerId)) {
            $errors['biller'] = 'Biller is required.';
        }

        if (!$user->isActive()) {
            $errors['user'] = 'User account is not active.';
        }

        if (!$idempotencyKey) {
            $errors['idempotency_key'] = 'Idempotency key is required.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    // Create payment and reserve money from wallet.
    private function createPendingPayment(
        User $user,
        int $billerId,
        string $accountNumber,
        int $amount,
        string $idempotencyKey
    ): BillPayment {
        return DB::transaction(function () use (
            $user,
            $billerId,
            $accountNumber,
            $amount,
            $idempotencyKey
        ) {
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                throw ValidationException::withMessages([
                    'wallet' => 'Wallet does not exist.',
                ]);
            }

            if ($wallet->is_frozen) {
                throw ValidationException::withMessages([
                    'wallet' => 'Wallet is frozen.',
                ]);
            }

            $balanceBefore = (int) $wallet->balance;

            if ($balanceBefore < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient wallet balance.',
                ]);
            }

            $balanceAfter = $balanceBefore - $amount;

            $payment = BillPayment::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'biller_id' => $billerId,
                'reference' => 'BILL-' . Str::uuid(),
                'account_number' => $accountNumber,
                'amount' => $amount,
                'status' => 'pending',
                'idempotency_key' => $idempotencyKey,
            ]);

            $wallet->update([
                'balance' => $balanceAfter,
            ]);

            $this->createTransaction(
                payment: $payment,
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                description: 'Bill payment pending'
            );

            $this->auditLogService->log(
                actor: $user,
                action: 'bill.created',
                description: 'Bill payment created and wallet debited.',
                model: $payment,
                oldValues: [
                    'wallet_balance' => $balanceBefore,
                ],
                newValues: [
                    'wallet_balance' => $balanceAfter,
                    'amount' => $amount,
                    'biller' => $billerId,
                    'status' => 'pending',
                ]
            );

            return $payment->fresh();
        });
    }

    // Create wallet ledger transaction.
    private function createTransaction(
        BillPayment $payment,
        int $balanceBefore,
        int $balanceAfter,
        string $description,
        string $direction = 'debit'
    ): void {
        WalletTransaction::create([
            'wallet_id' => $payment->wallet_id,
            'reference' => $payment->reference,
            'direction' => $direction,
            'amount' => (int) $payment->amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
        ]);
    }

    //Mock external bill provider.
    private function callMockBillProvider(
        int $billerId,
        string $accountNumber,
        int $amount
    ): array {
        if ($accountNumber === 'FAIL') {
            return [
                'success' => false,
                'message' => 'Mock bill provider rejected the payment.',
                'external_reference' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Bill payment successful.',
            'external_reference' => 'BILL-' . strtoupper(Str::random(12)),
        ];
    }

    // Mark payment as completed.
    public function completePayment(
        BillPayment $payment,
        string $externalReference
    ): BillPayment {
        return DB::transaction(function () use (
            $payment,
            $externalReference
        ) {
            $payment = BillPayment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status === 'completed') {
                return $payment;
            }

            if ($payment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'Only pending bill payments can be completed.',
                ]);
            }

            $payment->update([
                'status' => 'completed',
                'external_reference' => $externalReference,
                'completed_at' => now(),
                'failure_reason' => null,
            ]);

            $this->auditLogService->log(
                actor: User::find($payment->user_id),
                action: 'bill.paid',
                description: 'Bill payment completed successfully.',
                model: $payment,
                oldValues: [
                    'status' => 'pending',
                ],
                newValues: [
                    'status' => 'completed',
                    'external_reference' => $externalReference,
                ]
            );

            return $payment->fresh();
        });
    }

    // Fail payment and refund wallet.
    public function failPayment(
        BillPayment $payment,
        string $reason
    ): BillPayment {
        return DB::transaction(function () use (
            $payment,
            $reason
        ) {
            $payment = BillPayment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status === 'failed') {
                return $payment;
            }

            if ($payment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'Only pending bill payments can be failed.',
                ]);
            }

            $wallet = Wallet::whereKey($payment->wallet_id)
                ->lockForUpdate()
                ->firstOrFail();

            $balanceBefore = (int) $wallet->balance;
            $balanceAfter = $balanceBefore + (int) $payment->amount;

            $wallet->update([
                'balance' => $balanceAfter,
            ]);

            $this->createTransaction(
                payment: $payment,
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                description: 'Refund for failed bill payment',
                direction: 'credit'
            );

            $payment->update([
                'status' => 'failed',
                'failure_reason' => $reason,
            ]);

            $this->auditLogService->log(
                actor: User::find($payment->user_id),
                action: 'bill.failed',
                description: 'Bill payment failed and wallet was refunded.',
                model: $payment,
                oldValues: [
                    'status' => 'pending',
                ],
                newValues: [
                    'status' => 'failed',
                    'refund_amount' => (int) $payment->amount,
                    'failure_reason' => $reason,
                ]
            );

            return $payment->fresh();
        });
    }

    // Refund a payment.
    public function refundPayment(
        BillPayment $payment,
        string $reason = 'Payment refunded'
    ): BillPayment {
        return $this->failPayment($payment, $reason);
    }

    // Re-check pending payment with provider.
    public function verifyPayment(BillPayment $payment): BillPayment
    {
        if ($payment->status !== 'pending') {
            return $payment;
        }

        $result = $this->callMockBillProvider(
            billerId: $payment->biller_id,
            accountNumber: $payment->account_number,
            amount: (int) $payment->amount
        );

        if ($result['success']) {
            return $this->completePayment(
                $payment,
                $result['external_reference']
            );
        }

        return $this->failPayment(
            $payment,
            $result['message'] ?? 'Provider rejected payment.'
        );
    }

    // Provider response is unknown.
    private function markProviderUncertain(
        BillPayment $payment
    ): void {
        $reason = 'Provider response uncertain. Reconciliation required.';

        $payment->update([
            'failure_reason' => $reason,
        ]);

        $this->auditLogService->log(
            actor: User::find($payment->user_id),
            action: 'bill.provider_uncertain',
            description: 'Bill provider response was uncertain.',
            model: $payment,
            oldValues: [],
            newValues: [
                'status' => 'pending',
                'failure_reason' => $reason,
            ]
        );
    }
}
