<?php

namespace App\Listeners;

use App\Events\KycApproved;
use App\Notifications\KycStatusNotification;

class SendKycApprovedNotification
{
    public function handle(KycApproved $event): void
    {
        $event->user->notify(
            new KycStatusNotification('approved')
        );
    }
}
