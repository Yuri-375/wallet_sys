<?php

use App\Jobs\FraudRescan;
use App\Jobs\GenerateStatement;
use App\Jobs\VerifyBillPayment;
use App\Models\BillPayment;
use App\Models\User;
use Illuminate\Support\Facades\Schedule;

// Fraud scan

Schedule::job(new FraudRescan)
    ->hourly()
    ->name('fraud-scan');


// Monthly statements

Schedule::call(function () {

    $periodStart = now()
        ->startOfMonth()
        ->subMonth()
        ->toDateString();

    $periodEnd = now()
        ->startOfMonth()
        ->subDay()
        ->toDateString();

    User::query()
        ->where('kyc_status', 'approved')
        ->select('id')
        ->chunkById(100, function ($users) use (
            $periodStart,
            $periodEnd
        ) {

            foreach ($users as $user) {

                GenerateStatement::dispatch(
                    $user->id,
                    $periodStart,
                    $periodEnd
                )->onQueue('pdf');
            }
        });
})
    ->monthlyOn(1, '02:00')
    ->name('monthly-statements')
    ->withoutOverlapping();


// Verify pending bill payments

Schedule::call(function () {

    BillPayment::query()
        ->where('status', 'pending')
        ->where(
            'created_at',
            '<',
            now()->subMinutes(5)
        )
        ->where(
            'created_at',
            '>',
            now()->subDay()
        )
        ->limit(100)
        ->pluck('id')
        ->each(function ($id) {

            VerifyBillPayment::dispatch($id)
                ->onQueue('bills');
        });
})
    ->everyFiveMinutes()
    ->name('verify-bill-payments');


// Prune failed jobs

Schedule::command('queue:prune-failed', [
    '--hours' => 168,
])
    ->weekly()
    ->name('prune-failed-jobs');
