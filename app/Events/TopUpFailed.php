<?php

namespace App\Events;

use App\Models\TopUp;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopUpFailed implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TopUp $topUp,
        public string $reason
    ) {}

    public function broadcastAs(): string
    {
        return 'topup.failed';
    }

    public function broadcastWith(): array
    {
        return [
            'top_up_id' => $this->topUp->id,
            'reason' => $this->reason,
        ];
    }
}
