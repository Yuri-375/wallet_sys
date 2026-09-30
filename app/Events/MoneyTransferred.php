<?php

namespace App\Events;

use App\Models\Transfer;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MoneyTransferred implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Transfer $transfer
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'user.' . $this->transfer->sender_id
            ),
            new PrivateChannel(
                'user.' . $this->transfer->receiver_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'money.transferred';
    }
}
