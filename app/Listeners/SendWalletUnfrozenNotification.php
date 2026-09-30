<?php

namespace App\Listeners;

use App\Events\WalletUnfrozen;
use App\Notifications\WalletUnfrozenNotification;

class SendWalletUnfrozenNotification
{
    public function handle(WalletUnfrozen $event): void
    {
        $wallet = $event->wallet;

        $wallet->loadMissing('user');

        $wallet->user->notify(
            new WalletUnfrozenNotification($wallet)
        );
    }
}
