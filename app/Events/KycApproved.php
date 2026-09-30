<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KycApproved implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'user.' . $this->user->id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kyc.approved';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'message' => 'Your KYC has been approved.',
        ];
    }
}
