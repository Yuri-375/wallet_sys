<?php

namespace App\Listeners;

use App\Events\BillPaid;
use App\Notifications\BillPaidNotification;

class SendBillPaidNotification
{
    public function handle(BillPaid $event): void
    {
        $bill = $event->billPayment;

        $bill->loadMissing('user');

        $bill->user->notify(
            new BillPaidNotification($bill)
        );
    }
}
