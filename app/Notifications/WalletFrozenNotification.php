<?php

namespace App\Notifications;

use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletFrozenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Wallet $wallet
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
            ->subject('Wallet Frozen')
            ->greeting('Hello ' . $notifiable->name)
            ->line(
                'Your wallet has been frozen.'
            )
            ->line(
                'Please contact support for assistance.'
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'wallet_frozen',
            'wallet_id' => $this->wallet->id,
            'message' => 'Your wallet has been frozen.',
        ];
    }
}
