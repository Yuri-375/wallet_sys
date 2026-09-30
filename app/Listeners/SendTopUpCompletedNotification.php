<?php

namespace App\Listeners;

use App\Events\TopUpCompleted;
use App\Notifications\TopUpCompletedNotification;

class SendTopUpCompletedNotification
{
    public function handle(TopUpCompleted $event): void
    {
        $topUp = $event->topUp;

        $topUp->loadMissing('user');

        $topUp->user->notify(
            new TopUpCompletedNotification($topUp)
        );
    }
}
