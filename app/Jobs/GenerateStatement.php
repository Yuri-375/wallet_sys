<?php

namespace App\Jobs;

use App\Models\Statement;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

class GenerateStatement implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public string $periodStart,
        public string $periodEnd,
    ) {}

    public function handle(): void
    {
        $user = User::with('wallet')->find($this->userId);

        if (! $user || ! $user->wallet) {
            return;
        }

        $wallet = $user->wallet;

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate statements
        |--------------------------------------------------------------------------
        */

        $existing = Statement::where('wallet_id', $wallet->id)
            ->whereDate('period_start', $this->periodStart)
            ->whereDate('period_end', $this->periodEnd)
            ->first();

        if ($existing && $existing->status === 'generated') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate balances
        |--------------------------------------------------------------------------
        */

        $openingBalance = (int) WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->where('created_at', '<', $this->periodStart . ' 00:00:00')
            ->orderByDesc('id')
            ->value('balance_after');

        /*
        | If there were no transactions before the period,
        | the wallet's opening balance is 0.
        */

        $openingBalance = $openingBalance ?? 0;

        $transactions = WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->whereBetween(
                'created_at',
                [
                    $this->periodStart . ' 00:00:00',
                    $this->periodEnd . ' 23:59:59',
                ]
            );

        $totalCredits = (int) (clone $transactions)
            ->where('direction', 'credit')
            ->sum('amount');

        $totalDebits = (int) (clone $transactions)
            ->where('direction', 'debit')
            ->sum('amount');

        $closingBalance = $openingBalance
            + $totalCredits
            - $totalDebits;

        /*
        |--------------------------------------------------------------------------
        | Create / update statement
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $wallet,
            $openingBalance,
            $totalCredits,
            $totalDebits,
            $closingBalance
        ) {
            Statement::updateOrCreate(
                [
                    'wallet_id' => $wallet->id,
                    'period_start' => $this->periodStart,
                    'period_end' => $this->periodEnd,
                ],
                [
                    'user_id' => $this->userId,
                    'opening_balance' => $openingBalance,
                    'total_credits' => $totalCredits,
                    'total_debits' => $totalDebits,
                    'closing_balance' => $closingBalance,
                    'status' => 'generated',
                    'generated_at' => now(),
                ]
            );
        });
    }

    public function failed(Throwable $exception): void
    {
        Statement::where('wallet_id', optional(
            User::find($this->userId)?->wallet
        )?->id)
            ->whereDate('period_start', $this->periodStart)
            ->whereDate('period_end', $this->periodEnd)
            ->update([
                'status' => 'failed',
            ]);
    }
}
