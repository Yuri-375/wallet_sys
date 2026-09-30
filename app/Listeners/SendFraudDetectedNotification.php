<?php

namespace App\Listeners;

use App\Events\FraudDetected;
use App\Models\User;
use App\Notifications\FraudDetectedNotification;
use Illuminate\Support\Facades\Notification;

class SendFraudDetectedNotification
{
    public function handle(FraudDetected $event): void
    {
        $admins = User::role('admin')->get();

        Notification::send(
            $admins,
            new FraudDetectedNotification(
                $event->fraudReport
            )
        );
    }
}
