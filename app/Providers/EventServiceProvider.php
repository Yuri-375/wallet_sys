<?php

namespace App\Providers;

use App\Events\BillPaid;
use App\Events\FraudDetected;
use App\Events\KycApproved;
use App\Events\KycRejected;
use App\Events\MoneyDeposited;
use App\Events\MoneyTransferred;
use App\Events\WalletFrozen;
use App\Listeners\NotifyAdminsOfFraud;
use App\Listeners\NotifyBillPaid;
use App\Listeners\NotifyKycStatus;
use App\Listeners\NotifyTransferParties;
use App\Listeners\NotifyWalletFrozen;
use App\Listeners\QueueFraudRescan;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MoneyTransferred::class => [
            NotifyTransferParties::class,
            QueueFraudRescan::class,
        ],

        MoneyDeposited::class => [
            // No notification needed for top-ups by default — hook in later if desired.
        ],

        BillPaid::class => [
            NotifyBillPaid::class,
        ],

        KycApproved::class => [
            NotifyKycStatus::class,
        ],

        KycRejected::class => [
            NotifyKycStatus::class,
        ],

        WalletFrozen::class => [
            NotifyWalletFrozen::class,
        ],

        FraudDetected::class => [
            NotifyAdminsOfFraud::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
