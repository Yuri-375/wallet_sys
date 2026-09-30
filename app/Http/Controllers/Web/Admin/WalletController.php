<?php

namespace App\Http\Controllers\Web\Admin;

use App\Events\WalletFrozen;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    use RespondsWithFlash;

    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    // Wallet List
    public function index(Request $request)
    {
        $query = Wallet::with('user')
            ->latest();

        /*
         * Search by:
         * - name
         * - email
         * - phone
         */
        if ($request->filled('q')) {

            $q = $request
                ->string('q')
                ->toString();

            $query->whereHas(
                'user',
                function ($userQuery) use ($q) {
                    $userQuery
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                }
            );
        }

        // Filter frozen/unfrozen wallets.
        if ($request->filled('frozen')) {

            $query->where(
                'is_frozen',
                $request->boolean('frozen')
            );
        }

        $wallets = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.wallets.index',
            compact('wallets')
        );
    }

    // Show Wallet
    public function show(Wallet $wallet)
    {
        $wallet->load('user');

        // Wallet transactions.
        $transactions = WalletTransaction::where(
            'wallet_id',
            $wallet->id
        )
            ->latest()
            ->paginate(20);

        /*
         * Calculate ledger net balance.
         *
         * Credit = +amount
         * Debit  = -amount
         */
        $ledgerNet = WalletTransaction::where(
            'wallet_id',
            $wallet->id
        )
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN direction = 'credit'
                                THEN amount
                            ELSE -amount
                        END
                    ),
                    0
                ) AS net
            ")
            ->value('net');

        $ledgerNet = (int) $ledgerNet;

        /*
         * Compare ledger balance with wallet balance.
         */
        $drift =
            $ledgerNet -
            (int) $wallet->balance;

        return view(
            'admin.wallets.show',
            compact(
                'wallet',
                'transactions',
                'ledgerNet',
                'drift'
            )
        );
    }

    // Freeze Wallet

    public function freeze(
        Request $request,
        Wallet $wallet
    ) {
        $data = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $admin = $request->user();

        // Only admins should reach this method.
        if (!$admin->hasRole('admin')) {
            throw ValidationException::withMessages([
                'admin' => 'Only admins can freeze wallets.',
            ]);
        }

        // Don't create another freeze audit ifthe wallet is already frozen.
        if ($wallet->is_frozen) {
            throw ValidationException::withMessages([
                'wallet' =>
                'Wallet is already frozen.',
            ]);
        }

        DB::transaction(function () use (
            $wallet,
            $data,
            $admin
        ) {

            // Lock wallet before changing it.
            $lockedWallet = Wallet::whereKey(
                $wallet->id
            )
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedWallet->is_frozen) {
                throw ValidationException::withMessages([
                    'wallet' =>
                    'Wallet is already frozen.',
                ]);
            }

            $oldValues = [
                'is_frozen' => (bool) $lockedWallet->is_frozen,
                'frozen_at' => $lockedWallet->frozen_at,
                'freeze_reason' => $lockedWallet->freeze_reason,
            ];

            // Freeze wallet.
            $lockedWallet->update([
                'is_frozen' => true,
                'frozen_at' => now(),
                'freeze_reason' => $data['reason'],
            ]);

            // Audit.
            $this->auditLogService->log(
                actor: $admin,
                action: 'wallet.frozen',
                description: 'Admin froze wallet',
                model: $lockedWallet,
                oldValues: $oldValues,
                newValues: [
                    'is_frozen' => true,
                    'frozen_at' => $lockedWallet->frozen_at,
                    'freeze_reason' => $data['reason'],
                ]
            );

            DB::afterCommit(function () use (
                $lockedWallet
            ) {
                event(
                    new WalletFrozen(
                        $lockedWallet->fresh()
                    )
                );
            });
        });

        // Refresh model for redirect/view.
        $wallet->refresh();

        return $this->success(
            'Wallet frozen successfully.',
            'admin.wallets.show',
            [
                'wallet' => $wallet,
            ]
        );
    }

    // Unfreeze Wallet
    public function unfreeze(
        Request $request,
        Wallet $wallet
    ) {
        $admin = $request->user();

        if (!$admin->hasRole('admin')) {
            throw ValidationException::withMessages([
                'admin' =>
                'Only admins can unfreeze wallets.',
            ]);
        }

        if (!$wallet->is_frozen) {
            throw ValidationException::withMessages([
                'wallet' =>
                'Wallet is not frozen.',
            ]);
        }

        DB::transaction(function () use (
            $wallet,
            $admin
        ) {

            // Lock wallet.
            $lockedWallet = Wallet::whereKey(
                $wallet->id
            )
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedWallet->is_frozen) {
                throw ValidationException::withMessages([
                    'wallet' =>
                    'Wallet is not frozen.',
                ]);
            }

            $oldValues = [
                'is_frozen' =>
                (bool) $lockedWallet->is_frozen,
                'frozen_at' =>
                $lockedWallet->frozen_at,
                'freeze_reason' =>
                $lockedWallet->freeze_reason,
            ];

            // Unfreeze wallet.
            $lockedWallet->update([
                'is_frozen' => false,
                'frozen_at' => null,
                'freeze_reason' => null,
            ]);


            // Audit.

            $this->auditLogService->log(
                actor: $admin,
                action: 'wallet.unfrozen',
                description: 'Admin unfroze wallet',
                model: $lockedWallet,
                oldValues: $oldValues,
                newValues: [
                    'is_frozen' => false,
                    'frozen_at' => null,
                    'freeze_reason' => null,
                ]
            );
        });

        // Refresh model.
        $wallet->refresh();

        return $this->success(
            'Wallet unfrozen successfully.',
            'admin.wallets.show',
            [
                'wallet' => $wallet,
            ]
        );
    }
}
