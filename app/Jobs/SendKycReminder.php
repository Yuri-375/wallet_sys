<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\KycStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendKycReminder implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(): void
    {
        User::query()
            ->where('kyc_status', 'pending')
            ->where(
                'created_at',
                '<=',
                now()->subDays(3)
            )
            ->chunkById(100, function ($users) {

                foreach ($users as $user) {

                    $user->notify(
                        new KycStatusNotification('reminder')
                    );
                }
            });
    }
}
