<?php

namespace App\Events;

use App\Models\Wallet;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletFrozen implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Wallet $wallet
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'user.' . $this->wallet->user_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'wallet.frozen';
    }

    public function broadcastWith(): array
    {
        return [
            'wallet_id' => $this->wallet->id,
            'message' => 'Your wallet has been frozen.',
        ];
    }
}
