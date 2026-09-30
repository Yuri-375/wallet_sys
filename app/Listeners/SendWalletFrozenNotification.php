<?php

namespace App\Listeners;

use App\Events\WalletFrozen;
use App\Notifications\WalletFrozenNotification;

class SendWalletFrozenNotification
{
    public function handle(WalletFrozen $event): void
    {
        $wallet = $event->wallet;

        $wallet->loadMissing('user');

        $wallet->user->notify(
            new WalletFrozenNotification($wallet)
        );
    }
}
