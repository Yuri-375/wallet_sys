<?php

namespace App\Listeners;

use App\Events\KycRejected;
use App\Notifications\KycStatusNotification;

class SendKycRejectedNotification
{
    public function handle(KycRejected $event): void
    {
        $event->user->notify(
            new KycStatusNotification(
                'rejected',
                $event->reason
            )
        );
    }
}
