<?php

namespace App\Jobs;

use App\Models\Transfer;
use App\Services\FraudService;
use Illuminate\Contracts\Queue\ShouldQueue;

class FraudRescan implements ShouldQueue
{
    public function handle(FraudService $fraudService): void
    {
        $transfers = Transfer::where('status', 'completed')
            ->where('created_at', '>=', now()->subDay())
            ->get();

        foreach ($transfers as $transfer) {
            $fraudService->scanTransaction($transfer);
        }
    }
}
