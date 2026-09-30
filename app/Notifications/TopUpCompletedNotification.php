<?php

namespace App\Notifications;

use App\Models\TopUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TopUpCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TopUp $topUp
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'top_up_completed',
            'top_up_id' => $this->topUp->id,
            'amount' => $this->topUp->amount,
            'message' => 'Your wallet has been topped up successfully.',
        ];
    }
}
