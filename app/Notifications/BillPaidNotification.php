<?php

namespace App\Notifications;

use App\Models\BillPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BillPayment $billPayment
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
            ->subject('Bill Payment Successful')
            ->greeting('Hello ' . $notifiable->name)
            ->line(
                'Your bill payment of ' .
                    number_format($this->billPayment->amount) .
                    ' MMK was successful.'
            )
            ->line(
                'Bill Payment ID: ' .
                    $this->billPayment->id
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bill_paid',
            'bill_payment_id' => $this->billPayment->id,
            'amount' => $this->billPayment->amount,
            'message' => 'Your bill payment was successful.',
        ];
    }
}
