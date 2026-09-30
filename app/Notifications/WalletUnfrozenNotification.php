<?php

namespace App\Notifications;

use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletUnfrozenNotification extends Notification implements ShouldQueue
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
            ->subject('Wallet Unfrozen')
            ->greeting('Hello ' . $notifiable->name)
            ->line(
                'Your wallet has been unfrozen.'
            )
            ->line(
                'You can now use your wallet again.'
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'wallet_unfrozen',
            'wallet_id' => $this->wallet->id,
            'message' => 'Your wallet has been unfrozen.',
        ];
    }
}
