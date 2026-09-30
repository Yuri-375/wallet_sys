<?php

namespace App\Services;

use App\Events\MoneyTransferred;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransferService
{
    public function __construct(
        private FraudService $fraudService,
        private AuditLogService $auditLogService
    ) {}

    // Create transfer
    public function createTransfer(
        User $sender,
        User $receiver,
        int $amount,
        ?string $idempotencyKey = null,
        ?string $description = null
    ): Transfer {
        $this->validateTransfer($sender, $receiver, $amount, $idempotencyKey);

        if ($this->findByIdempotencyKey($idempotencyKey)) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'This idempotency key has already been used.',
            ]);
        }

        $fraud = $this->fraudService->checkTransfer(
            sender: $sender,
            receiver: $receiver,
            amount: $amount
        );

        if ($fraud['otp_required'] ?? false) {
            return $this->createOtpTransfer(
                $sender,
                $receiver,
                $amount,
                $idempotencyKey,
                $description,
                $fraud['reasons'] ?? []
            );
        }

        return $this->completeTransfer(
            $sender,
            $receiver,
            $amount,
            $idempotencyKey,
            $description
        );
    }

    // Verify OTP
    public function verifyTransferOtp(
        User $sender,
        Transfer $transfer,
        string $otp
    ): Transfer {
        $this->authorizeOtp($sender, $transfer);

        return DB::transaction(function () use ($sender, $transfer, $otp) {
            $transfer = $this->lockTransfer($transfer);

            $this->validateOtp($transfer);

            if (now()->greaterThan($transfer->otp_expires_at)) {
                $transfer->update(['status' => 'failed']);

                throw ValidationException::withMessages([
                    'otp' => 'OTP has expired.',
                ]);
            }

            if ($transfer->otp_attempts >= 5) {
                $transfer->update(['status' => 'failed']);

                throw ValidationException::withMessages([
                    'otp' => 'Maximum OTP attempts exceeded.',
                ]);
            }

            $transfer->increment('otp_attempts');
            $transfer->refresh();

            if (!Hash::check($otp, $transfer->otp_hash)) {
                $this->auditLogService->log(
                    actor: $sender,
                    action: 'transfer.otp_failed',
                    description: 'Invalid transfer OTP.',
                    model: $transfer,
                    oldValues: [],
                    newValues: [
                        'otp_attempts' => $transfer->otp_attempts,
                    ]
                );

                if ($transfer->otp_attempts >= 5) {
                    $transfer->update(['status' => 'failed']);
                }

                throw ValidationException::withMessages([
                    'otp' => 'Invalid OTP.',
                ]);
            }

            $receiver = User::findOrFail($transfer->receiver_id);

            return $this->completeTransfer(
                sender: $sender,
                receiver: $receiver,
                amount: (int) $transfer->amount,
                idempotencyKey: $transfer->idempotency_key,
                description: $transfer->description,
                existingTransferId: $transfer->id
            );
        });
    }

    // Complete transfer
    public function completeTransfer(
        User $sender,
        User $receiver,
        int $amount,
        string $idempotencyKey,
        ?string $description = null,
        ?int $existingTransferId = null
    ): Transfer {
        return DB::transaction(function () use (
            $sender,
            $receiver,
            $amount,
            $idempotencyKey,
            $description,
            $existingTransferId
        ) {
            $transfer = $existingTransferId
                ? $this->lockTransferById($existingTransferId)
                : $this->getOrCreateTransfer(
                    $sender,
                    $receiver,
                    $amount,
                    $idempotencyKey,
                    $description
                );

            if ($transfer->status === 'completed') {
                return $transfer;
            }

            $this->validateCompletion($transfer, $sender, $receiver, $amount);

            [$senderWallet, $receiverWallet] =
                $this->lockWallets($sender, $receiver);

            $balances = $this->calculateBalances(
                $senderWallet,
                $receiverWallet,
                $amount
            );

            $senderWallet->update([
                'balance' => $balances['sender_after'],
            ]);

            $receiverWallet->update([
                'balance' => $balances['receiver_after'],
            ]);

            $oldStatus = $transfer->status;

            $transfer->update([
                'status' => 'completed',
                'completed_at' => now(),
                'description' => $description,
            ]);

            $this->createLedger(
                $senderWallet,
                $receiverWallet,
                $amount,
                $balances,
                $transfer->reference
            );

            $this->fraudService->scanTransaction(
                transfer: $transfer,
            );

            $this->auditLogService->log(
                actor: $sender,
                action: 'transfer.completed',
                description: 'Wallet transfer completed successfully.',
                model: $transfer,
                oldValues: [
                    'sender_balance' => $balances['sender_before'],
                    'receiver_balance' => $balances['receiver_before'],
                    'status' => $oldStatus,
                ],
                newValues: [
                    'sender_balance' => $balances['sender_after'],
                    'receiver_balance' => $balances['receiver_after'],
                    'amount' => $amount,
                    'receiver_id' => $receiver->id,
                    'reference' => $transfer->reference,
                    'status' => 'completed',
                ]
            );

            DB::afterCommit(
                fn() => event(new MoneyTransferred($transfer))
            );

            return $transfer->fresh(['sender', 'receiver']);
        });
    }

    // Create OTP transfer
    private function createOtpTransfer(
        User $sender,
        User $receiver,
        int $amount,
        string $idempotencyKey,
        ?string $description,
        array $fraudReasons
    ): Transfer {
        $otp = (string) random_int(100000, 999999);

        $transfer = Transfer::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
            'reference' => $this->generateReference(),
            'status' => 'otp_required',
            'idempotency_key' => $idempotencyKey,
            'description' => $description,
            'otp_hash' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(5),
            'otp_attempts' => 0,
        ]);

        $transfer->setAttribute('development_otp', $otp);

        return $transfer;
    }


    // Get transfer
    private function getOrCreateTransfer(
        User $sender,
        User $receiver,
        int $amount,
        string $idempotencyKey,
        ?string $description
    ): Transfer {
        $transfer = Transfer::where(
            'idempotency_key',
            $idempotencyKey
        )
            ->lockForUpdate()
            ->first();

        if ($transfer) {
            return $transfer;
        }

        return Transfer::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
            'reference' => $this->generateReference(),
            'status' => 'pending',
            'idempotency_key' => $idempotencyKey,
            'description' => $description,
        ]);
    }

    // Validate transfer
    private function validateTransfer(
        User $sender,
        User $receiver,
        int $amount,
        ?string $idempotencyKey
    ): void {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Transfer amount must be greater than zero.',
            ]);
        }

        if ($sender->id === $receiver->id) {
            throw ValidationException::withMessages([
                'receiver' => 'You cannot transfer money to yourself.',
            ]);
        }

        if (!$sender->isActive()) {
            throw ValidationException::withMessages([
                'sender' => 'Sender account is not active.',
            ]);
        }

        if (!$receiver->isActive()) {
            throw ValidationException::withMessages([
                'receiver' => 'Receiver account is not active.',
            ]);
        }

        if (!$idempotencyKey) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Idempotency key is required.',
            ]);
        }
    }

    // Validate completion
    private function validateCompletion(
        Transfer $transfer,
        User $sender,
        User $receiver,
        int $amount
    ): void {
        if (!in_array($transfer->status, ['pending', 'otp_required'], true)) {
            throw ValidationException::withMessages([
                'transfer' => 'This transfer cannot be completed.',
            ]);
        }

        if ($sender->id === $receiver->id) {
            throw ValidationException::withMessages([
                'receiver' => 'Cannot transfer to yourself.',
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Transfer amount must be greater than zero.',
            ]);
        }
    }

    // Lock wallets
    private function lockWallets(
        User $sender,
        User $receiver
    ): array {
        $wallets = Wallet::whereIn('user_id', [
            $sender->id,
            $receiver->id,
        ])
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('user_id');

        $senderWallet = $wallets->get($sender->id);
        $receiverWallet = $wallets->get($receiver->id);

        if (!$senderWallet) {
            throw ValidationException::withMessages([
                'sender_wallet' => 'Sender wallet does not exist.',
            ]);
        }

        if (!$receiverWallet) {
            throw ValidationException::withMessages([
                'receiver_wallet' => 'Receiver wallet does not exist.',
            ]);
        }

        if ($senderWallet->is_frozen) {
            throw ValidationException::withMessages([
                'sender_wallet' => 'Sender wallet is frozen.',
            ]);
        }

        if ($receiverWallet->is_frozen) {
            throw ValidationException::withMessages([
                'receiver_wallet' => 'Receiver wallet is frozen.',
            ]);
        }

        return [$senderWallet, $receiverWallet];
    }

    // Calculate balances
    private function calculateBalances(
        Wallet $senderWallet,
        Wallet $receiverWallet,
        int $amount
    ): array {
        $senderBefore = (int) $senderWallet->balance;
        $receiverBefore = (int) $receiverWallet->balance;

        if ($senderBefore < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient wallet balance.',
            ]);
        }

        return [
            'sender_before' => $senderBefore,
            'sender_after' => $senderBefore - $amount,
            'receiver_before' => $receiverBefore,
            'receiver_after' => $receiverBefore + $amount,
        ];
    }

    // Create ledger
    private function createLedger(
        Wallet $senderWallet,
        Wallet $receiverWallet,
        int $amount,
        array $balances,
        string $reference
    ): void {
        WalletTransaction::create([
            'wallet_id' => $senderWallet->id,
            'direction' => 'debit',
            'amount' => $amount,
            'balance_before' => $balances['sender_before'],
            'balance_after' => $balances['sender_after'],
            'reference' => $reference,
            'description' => 'Wallet transfer sent',
        ]);

        WalletTransaction::create([
            'wallet_id' => $receiverWallet->id,
            'direction' => 'credit',
            'amount' => $amount,
            'balance_before' => $balances['receiver_before'],
            'balance_after' => $balances['receiver_after'],
            'reference' => $reference,
            'description' => 'Wallet transfer received',
        ]);
    }

    // OTP authorization
    private function authorizeOtp(
        User $sender,
        Transfer $transfer
    ): void {
        if ($transfer->sender_id !== $sender->id) {
            throw ValidationException::withMessages([
                'transfer' => 'You are not allowed to verify this transfer.',
            ]);
        }

        if ($transfer->status !== 'otp_required') {
            throw ValidationException::withMessages([
                'transfer' => 'This transfer does not require OTP verification.',
            ]);
        }
    }

    // Validate OTP
    private function validateOtp(Transfer $transfer): void
    {
        if ($transfer->status !== 'otp_required') {
            throw ValidationException::withMessages([
                'transfer' => 'This transfer is no longer awaiting OTP verification.',
            ]);
        }

        if (!$transfer->otp_expires_at) {
            throw ValidationException::withMessages([
                'otp' => 'OTP expiry time is missing.',
            ]);
        }
    }

    // Find transfer by key
    private function findByIdempotencyKey(string $key): ?Transfer
    {
        return Transfer::where('idempotency_key', $key)->first();
    }

    // Lock transfer
    private function lockTransfer(Transfer $transfer): Transfer
    {
        return $this->lockTransferById($transfer->id);
    }

    private function lockTransferById(int $id): Transfer
    {
        return Transfer::whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    // Generate reference
    private function generateReference(): string
    {
        return 'TRX-' . strtoupper(Str::random(12));
    }
}
