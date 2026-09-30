<?php

namespace App\Notifications;

use App\Models\Transfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MoneyReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Transfer $transfer
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
            ->subject('Money Received')
            ->greeting('Hello ' . $notifiable->name)
            ->line(
                'You received ' .
                    number_format($this->transfer->amount) .
                    ' MMK.'
            )
            ->line(
                'Transfer ID: ' .
                    $this->transfer->id
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'money_received',
            'transfer_id' => $this->transfer->id,
            'amount' => $this->transfer->amount,
            'message' => 'You received money.',
        ];
    }
}
