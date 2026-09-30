<?php

namespace App\Notifications;

use App\Models\TopUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopUpFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TopUp $topUp,
        public string $reason
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Top-up Failed')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your top-up failed.')
            ->line('Reason: ' . $this->reason)
            ->line('Top-up ID: ' . $this->topUp->id);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'topup_failed',
            'top_up_id' => $this->topUp->id,
            'reason' => $this->reason,
            'message' => 'Your top-up failed.',
        ];
    }
}
