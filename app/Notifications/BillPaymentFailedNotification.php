<?php

namespace App\Notifications;

use App\Models\BillPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillPaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BillPayment $billPayment,
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
            ->subject('Bill Payment Failed')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your bill payment failed.')
            ->line('Reason: ' . $this->reason)
            ->line(
                'Bill Payment ID: ' .
                    $this->billPayment->id
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bill_payment_failed',
            'bill_payment_id' => $this->billPayment->id,
            'reason' => $this->reason,
            'message' => 'Your bill payment failed.',
        ];
    }
}
