<?php

namespace App\Listeners;

use App\Events\TopUpFailed;
use App\Notifications\TopUpFailedNotification;

class SendTopUpFailedNotification
{
    public function handle(TopUpFailed $event): void
    {
        $topUp = $event->topUp;

        $topUp->loadMissing('user');

        $topUp->user->notify(
            new TopUpFailedNotification(
                $topUp,
                $event->reason
            )
        );
    }
}
