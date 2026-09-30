<?php

namespace App\Events;

use App\Models\BillPayment;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillPaid implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BillPayment $billPayment
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'user.' . $this->billPayment->user_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'bill.paid';
    }

    public function broadcastWith(): array
    {
        return [
            'bill_payment_id' => $this->billPayment->id,
            'amount' => $this->billPayment->amount,
            'message' => 'Your bill payment was successful.',
        ];
    }
}
