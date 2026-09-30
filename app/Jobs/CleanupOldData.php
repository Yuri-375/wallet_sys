<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CleanupOldData implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(): void
    {
        /*
         * Cleanup temporary/non-financial data here.
         *
         * NEVER delete:
         *
         * - wallet_transactions
         * - completed transfers
         * - completed top-ups
         * - immutable ledger records
         * - audit records required for compliance
         */
    }
}
