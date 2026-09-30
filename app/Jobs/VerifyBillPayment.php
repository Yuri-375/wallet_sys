<?php

namespace App\Jobs;

use App\Models\BillPayment;
use App\Services\BillService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class VerifyBillPayment implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $paymentId
    ) {}

    public function handle(
        BillService $billService
    ): void {

        $payment = BillPayment::find($this->paymentId);

        if (! $payment) {
            return;
        }

        if ($payment->status !== 'pending') {
            return;
        }

        $billService->verifyPayment($payment);
    }

    public function failed(Throwable $exception): void {}
}
