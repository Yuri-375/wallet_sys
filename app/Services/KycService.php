<?php

namespace App\Services;

use App\Events\KycApproved;
use App\Events\KycRejected;
use App\Models\KycDocument;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class KycService
{
    public function __construct(
        private WalletService $walletService,
        private AuditLogService $auditLogService
    ) {}

    // Submit KYC Document
    public function submit(
        User $user,
        string $documentType,
        UploadedFile $file
    ): KycDocument {
        /*
         * Store file first.
         */
        $path = $file->store(
            'kyc/' . $user->id,
            'local'
        );

        try {
            $document = DB::transaction(function () use (
                $user,
                $documentType,
                $path
            ) {

                //Prevent duplicate pending document of the same type.
                $existing = KycDocument::where(
                    'user_id',
                    $user->id
                )
                    ->where('document_type', $documentType)
                    ->where('status', 'pending')
                    ->exists();

                if ($existing) {
                    throw ValidationException::withMessages([
                        'document_type' =>
                        'A document of this type is already pending.',
                    ]);
                }

                /*
                 * Create KYC document.
                 */
                $document = KycDocument::create([
                    'user_id' => $user->id,
                    'document_type' => $documentType,
                    'document_path' => $path,
                    'status' => 'pending',
                ]);

                // User KYC status becomes pending.
                $user->update([
                    'kyc_status' => 'pending',
                ]);

                // Audit.
                $this->auditLogService->log(
                    actor: $user,
                    action: 'kyc.submitted',
                    description: 'User uploaded KYC document',
                    model: $document,
                    newValues: [
                        'document_type' => $documentType,
                        'status' => 'pending',
                    ]
                );

                return $document;
            });

            return $document;
        } catch (Throwable $e) {
            // If DB operation fails, delete uploaded file.
            Storage::disk('local')->delete($path);

            throw $e;
        }
    }

    // Approve KYC
    public function approve(
        User $verifier,
        User $user
    ): User {
        if (
            !$verifier->hasRole('agent') &&
            !$verifier->hasRole('admin')
        ) {
            throw ValidationException::withMessages([
                'verifier' => 'Only agents or admins can approve KYC.',
            ]);
        }

        return DB::transaction(function () use (
            $verifier,
            $user
        ) {
            $user->refresh();

            if ($user->kyc_status !== 'pending') {
                throw ValidationException::withMessages([
                    'kyc' => 'User KYC is not pending.',
                ]);
            }

            $oldStatus = $user->kyc_status;

            // Approve KYC.
            $user->update([
                'kyc_status' => 'approved',
                'kyc_verified_at' => now(),
                'verified_by' => $verifier->id,
            ]);

            // Create wallet.
            $wallet = $this->walletService->createWallet($user);

            // KYC audit.
            $this->auditLogService->log(
                actor: $verifier,
                action: 'kyc.approved',
                description: 'KYC application approved',
                model: $user,
                oldValues: [
                    'kyc_status' => $oldStatus,
                ],
                newValues: [
                    'kyc_status' => 'approved',
                    'verified_by' => $verifier->id,
                ]
            );

            // Wallet creation audit.
            $this->auditLogService->log(
                actor: $verifier,
                action: 'wallet.created',
                description: 'Wallet created after KYC approval',
                model: $wallet,
                newValues: [
                    'user_id' => $user->id,
                    'currency' => $wallet->currency,
                    'balance' => $wallet->balance,
                ]
            );

            /*
             * Event executes after successful commit.
             */
            DB::afterCommit(function () use ($user) {
                event(new KycApproved($user->fresh()));
            });

            return $user->fresh();
        });
    }

    // Reject KYC

    public function reject(
        User $verifier,
        User $user,
        string $reason
    ): User {
        if (
            !$verifier->hasRole('agent') &&
            !$verifier->hasRole('admin')
        ) {
            throw ValidationException::withMessages([
                'verifier' => 'Only agents or admins can reject KYC.',
            ]);
        }

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'Rejection reason is required.',
            ]);
        }

        return DB::transaction(function () use (
            $verifier,
            $user,
            $reason
        ) {
            $user->refresh();

            if ($user->kyc_status !== 'pending') {
                throw ValidationException::withMessages([
                    'kyc' => 'User KYC is not pending.',
                ]);
            }

            $oldStatus = $user->kyc_status;

            // Reject KYC.
            $user->update([
                'kyc_status' => 'rejected',
                'kyc_verified_at' => now(),
                'verified_by' => $verifier->id,
            ]);

            /*
             * IMPORTANT:
             *
             * This must be kyc.rejected,
             * not kyc.approved.
             */
            $this->auditLogService->log(
                actor: $verifier,
                action: 'kyc.rejected',
                description: 'KYC application rejected',
                model: $user,
                oldValues: [
                    'kyc_status' => $oldStatus,
                ],
                newValues: [
                    'kyc_status' => 'rejected',
                    'rejection_reason' => $reason,
                    'verified_by' => $verifier->id,
                ]
            );

            DB::afterCommit(function () use (
                $user,
                $reason
            ) {
                event(new KycRejected(
                    $user->fresh(),
                    $reason
                ));
            });

            return $user->fresh();
        });
    }
}
