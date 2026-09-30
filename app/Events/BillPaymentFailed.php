<?php

namespace App\Events;

use App\Models\BillPayment;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillPaymentFailed implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BillPayment $billPayment,
        public string $reason
    ) {}

    public function broadcastAs(): string
    {
        return 'billpayment.failed';
    }

    public function broadcastWith(): array
    {
        return [
            'top_up_id' => $this->billPayment->id,
            'reason' => $this->reason,
        ];
    }
}
