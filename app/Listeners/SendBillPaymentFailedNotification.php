<?php

namespace App\Listeners;

use App\Events\BillPaymentFailed;
use App\Notifications\BillPaymentFailedNotification;

class SendBillPaymentFailedNotification
{
    public function handle(BillPaymentFailed $event): void
    {
        $bill = $event->billPayment;

        $bill->loadMissing('user');

        $bill->user->notify(
            new BillPaymentFailedNotification(
                $bill,
                $event->reason
            )
        );
    }
}
