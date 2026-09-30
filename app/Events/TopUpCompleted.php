<?php

namespace App\Events;

use App\Models\TopUp;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopUpCompleted implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TopUp $topUp
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'user.' . $this->topUp->user_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'topup.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'top_up_id' => $this->topUp->id,
            'amount' => $this->topUp->amount,
            'message' => 'Your top-up was successful.',
        ];
    }
}
