<?php

namespace App\Listeners;

use App\Events\MoneyTransferred;
use App\Notifications\MoneyReceivedNotification;
use App\Notifications\MoneyTransferredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTransferNotification
{
    public function handle(MoneyTransferred $event): void
    {
        $transfer = $event->transfer;

        $transfer->loadMissing([
            'sender',
            'receiver',
        ]);

        $transfer->sender->notify(
            new MoneyTransferredNotification($transfer)
        );

        $transfer->receiver->notify(
            new MoneyReceivedNotification($transfer)
        );
    }
}
