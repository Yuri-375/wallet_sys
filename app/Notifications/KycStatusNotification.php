<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $status,
        public ?string $reason = null
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->status === 'reminder') {
            return (new MailMessage)
                ->subject('KYC Reminder')
                ->greeting('Hello ' . $notifiable->name)
                ->line(
                    'Your KYC verification is still pending.'
                )
                ->line(
                    'Please complete your KYC documents.'
                );
        }

        $mail = (new MailMessage)
            ->subject('KYC Status Updated')
            ->greeting('Hello ' . $notifiable->name)
            ->line(
                'Your KYC status is now: ' .
                    strtoupper($this->status)
            );

        if ($this->reason) {
            $mail->line(
                'Reason: ' . $this->reason
            );
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'kyc_status',
            'status' => $this->status,
            'reason' => $this->reason,
            'message' => $this->status === 'reminder'
                ? 'Your KYC verification is still pending.'
                : 'Your KYC status has been updated.',
        ];
    }
}
