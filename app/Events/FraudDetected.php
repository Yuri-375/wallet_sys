<?php

namespace App\Events;

use App\Models\FraudReport;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FraudDetected implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FraudReport $fraudReport
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.fraud'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'fraud.detected';
    }

    public function broadcastWith(): array
    {
        return [
            'fraud_report_id' => $this->fraudReport->id,
            'message' => 'New fraud report detected.',
        ];
    }
}
