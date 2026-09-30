<?php

namespace App\Services;

use App\Events\TopUpCompleted;
use App\Models\TopUp;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    public function getWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'currency' => 'MMK',
                'balance' => 0,
                'is_frozen' => false,
            ]
        );
    }

    public function createWallet(User $user): Wallet
    {
        if ($wallet = Wallet::where('user_id', $user->id)->first()) {
            return $wallet;
        }

        try {
            return DB::transaction(function () use ($user) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'currency' => 'MMK',
                    'balance' => 0,
                    'is_frozen' => false,
                ]);

                $this->auditLog(
                    $user,
                    'wallet.created',
                    'Wallet created for user.',
                    $wallet,
                    [],
                    [
                        'user_id' => $user->id,
                        'currency' => 'MMK',
                        'balance' => 0,
                    ]
                );

                return $wallet;
            });
        } catch (QueryException $e) {
            if (!$this->isDuplicateKey($e)) {
                throw $e;
            }

            return Wallet::where('user_id', $user->id)->firstOrFail();
        }
    }

    public function credit(
        Wallet $wallet,
        int $amount,
        ?User $actor = null,
        ?string $description = null
    ): Wallet {
        $this->validateAmount($amount);

        return DB::transaction(function () use (
            $wallet,
            $amount,
            $actor,
            $description
        ) {
            $wallet = $this->lockWallet($wallet);
            $this->ensureWalletNotFrozen($wallet);

            $this->changeBalance(
                wallet: $wallet,
                amount: $amount,
                direction: 'credit',
                description: $description ?? 'Wallet credit',
                actor: $actor
            );

            return $wallet;
        });
    }

    public function debit(
        Wallet $wallet,
        int $amount,
        ?User $actor = null,
        ?string $description = null
    ): Wallet {
        $this->validateAmount($amount);

        return DB::transaction(function () use (
            $wallet,
            $amount,
            $actor,
            $description
        ) {
            $wallet = $this->lockWallet($wallet);
            $this->ensureWalletNotFrozen($wallet);

            if ($wallet->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient wallet balance.',
                ]);
            }

            $this->changeBalance(
                wallet: $wallet,
                amount: -$amount,
                direction: 'debit',
                description: $description ?? 'Wallet debit',
                actor: $actor
            );

            return $wallet;
        });
    }

    public function agentTopUp(
        User $agent,
        User $user,
        int $amount,
        string $method = 'cash',
        ?string $reference = null,
        ?string $idempotencyKey = null
    ): TopUp {
        $this->ensureAgentCanTopUp($agent);
        $this->validateAmount($amount);
        $this->validateIdempotencyKey($idempotencyKey);

        if ($existing = $this->findTopUp($idempotencyKey)) {
            return $existing;
        }

        return DB::transaction(function () use (
            $agent,
            $user,
            $amount,
            $method,
            $reference,
            $idempotencyKey
        ) {
            $this->ensureKycApproved($user);

            $wallet = $this->getLockedWallet($user);
            $this->ensureWalletNotFrozen($wallet);

            $topUp = TopUp::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference ?: 'TOP-' . strtoupper(Str::random(12)),
                'idempotency_key' => $idempotencyKey,
                'status' => 'completed',
                'processed_by' => $agent->id,
                'completed_at' => now(),
            ]);

            $this->changeBalance(
                wallet: $wallet,
                amount: $amount,
                direction: 'credit',
                description: 'Cash top-up',
                reference: $topUp->reference
            );

            $this->auditLog(
                $agent,
                'topup.completed',
                'Agent completed a cash wallet top-up.',
                $topUp,
                ['wallet_balance' => $wallet->balance - $amount],
                [
                    'wallet_balance' => $wallet->balance,
                    'amount' => $amount,
                    'method' => $method,
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                ]
            );

            DB::afterCommit(
                fn() => event(new TopUpCompleted($topUp))
            );

            return $topUp;
        });
    }

    public function getBalance(User $user): int
    {
        return (int) $this->getWallet($user)->balance;
    }

    public function freeze(
        Wallet $wallet,
        User $admin,
        ?string $reason = null
    ): Wallet {
        $this->ensureAdmin($admin);

        return DB::transaction(function () use ($wallet, $admin, $reason) {
            $wallet = $this->lockWallet($wallet);

            if ($wallet->is_frozen) {
                return $wallet;
            }

            $oldValues = [
                'is_frozen' => $wallet->is_frozen,
                'freeze_reason' => $wallet->freeze_reason,
            ];

            $wallet->update([
                'is_frozen' => true,
                'freeze_reason' => $reason,
                'frozen_at' => now(),
                'frozen_by' => $admin->id,
            ]);

            $this->auditLog(
                $admin,
                'wallet.frozen',
                'Wallet was frozen by admin.',
                $wallet,
                $oldValues,
                [
                    'is_frozen' => true,
                    'freeze_reason' => $reason,
                ]
            );

            return $wallet;
        });
    }

    public function unfreeze(
        Wallet $wallet,
        User $admin
    ): Wallet {
        $this->ensureAdmin($admin);

        return DB::transaction(function () use ($wallet, $admin) {
            $wallet = $this->lockWallet($wallet);

            if (!$wallet->is_frozen) {
                return $wallet;
            }

            $oldValues = [
                'is_frozen' => true,
                'freeze_reason' => $wallet->freeze_reason,
            ];

            $wallet->update([
                'is_frozen' => false,
                'freeze_reason' => null,
                'frozen_at' => null,
                'frozen_by' => null,
            ]);

            $this->auditLog(
                $admin,
                'wallet.unfrozen',
                'Wallet was unfrozen by admin.',
                $wallet,
                $oldValues,
                [
                    'is_frozen' => false,
                    'freeze_reason' => null,
                ]
            );

            return $wallet;
        });
    }

    private function changeBalance(
        Wallet $wallet,
        int $amount,
        string $direction,
        string $description,
        ?User $actor = null,
        ?string $reference = null
    ): void {
        $before = (int) $wallet->balance;
        $after = $before + $amount;

        $wallet->update([
            'balance' => $after,
        ]);

        $this->createTransaction(
            wallet: $wallet,
            direction: $direction,
            amount: abs($amount),
            balanceBefore: $before,
            balanceAfter: $after,
            reference: $reference,
            description: $description
        );

        $this->auditLog(
            $actor,
            "wallet.$direction",
            $description,
            $wallet,
            ['balance' => $before],
            [
                'balance' => $after,
                'amount' => abs($amount),
            ]
        );
    }

    private function lockWallet(Wallet $wallet): Wallet
    {
        return Wallet::whereKey($wallet->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function getLockedWallet(User $user): Wallet
    {
        return Wallet::where('user_id', $user->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function createTransaction(
        Wallet $wallet,
        string $direction,
        int $amount,
        int $balanceBefore,
        int $balanceAfter,
        ?string $reference = null,
        ?string $description = null
    ): WalletTransaction {
        return WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'direction' => $direction,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference' => $reference,
            'description' => $description,
        ]);
    }

    private function findTopUp(?string $idempotencyKey): ?TopUp
    {
        return TopUp::where(
            'idempotency_key',
            $idempotencyKey
        )->first();
    }

    private function validateAmount(int $amount): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be greater than zero.',
            ]);
        }
    }

    private function validateIdempotencyKey(?string $key): void
    {
        if (!$key) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Idempotency key is required.',
            ]);
        }
    }

    private function ensureWalletNotFrozen(Wallet $wallet): void
    {
        if ($wallet->is_frozen) {
            throw ValidationException::withMessages([
                'wallet' => 'Wallet is frozen.',
            ]);
        }
    }

    private function ensureAgentCanTopUp(User $agent): void
    {
        if (!$agent->hasAnyRole(['agent', 'admin'])) {
            throw ValidationException::withMessages([
                'agent' => 'Only an agent or admin can perform a cash top-up.',
            ]);
        }
    }

    private function ensureKycApproved(User $user): void
    {
        if ($user->kyc_status !== 'approved') {
            throw ValidationException::withMessages([
                'user' => 'User must have approved KYC before receiving a top-up.',
            ]);
        }
    }

    private function ensureAdmin(User $admin): void
    {
        if (!$admin->hasRole('admin')) {
            throw ValidationException::withMessages([
                'admin' => 'Only admins can perform this action.',
            ]);
        }
    }

    private function auditLog(
        ?User $actor,
        string $action,
        string $description,
        mixed $model,
        array $oldValues,
        array $newValues
    ): void {
        $this->auditLogService->log(
            actor: $actor,
            action: $action,
            description: $description,
            model: $model,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }

    private function isDuplicateKey(QueryException $e): bool
    {
        return $e->getCode() === '23000'
            && (int) ($e->errorInfo[1] ?? 0) === 1062;
    }
}
