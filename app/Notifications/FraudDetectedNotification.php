<?php

namespace App\Notifications;

use App\Models\FraudReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FraudDetectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FraudReport $fraudReport
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
            ->subject('Fraud Alert')
            ->greeting('Hello ' . $notifiable->name)
            ->line(
                'A new fraud report requires investigation.'
            )
            ->line(
                'Fraud Report ID: ' .
                    $this->fraudReport->id
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'fraud_detected',
            'fraud_report_id' => $this->fraudReport->id,
            'message' => 'New fraud report detected.',
        ];
    }
}
