protected function schedule(Schedule $schedule): void
{
    $schedule->job(new \App\Jobs\ReconcileLedger())->everyFifteenMinutes();
    $schedule->job(new \App\Jobs\CancelExpiredTransfers())->everyFiveMinutes();
    $schedule->job(new \App\Jobs\RunFraudScan())->hourly();
    $schedule->job(new \App\Jobs\GenerateMonthlyStatements())->monthlyOn(1, '02:00');

    $schedule->call(function () {
        \App\Models\BillPayment::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(5))
            ->where('created_at', '>', now()->subDay())
            ->limit(100)
            ->pluck('id')
            ->each(fn ($id) => \App\Jobs\VerifyBillPayment::dispatch($id)->onQueue('bills'));
    })->everyFiveMinutes();
}