<?php

namespace App\Services;

use App\Models\FraudRule;
use App\Models\FraudReport;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FraudService
{
    // Check Transfer Before Execution
    public function checkTransfer(
        User $sender,
        User $receiver,
        int $amount
    ): array {
        $reasons = [];

        // Rule 1: More than 5 transfers in 1 minute.
        $recentTransfers = Transfer::where(
            'sender_id',
            $sender->id
        )
            ->where('created_at', '>=', now()->subMinute())
            ->whereIn('status', [
                'completed',
                'otp_required',
            ])
            ->count();

        if ($recentTransfers >= 5) {
            $reasons[] =
                'More than 5 transfers attempted within 1 minute.';
        }

        // Rule 2: Single transfer > 50,000 MMK.
        if ($amount > 50000) {
            $reasons[] =
                'Transfer amount exceeds high-value threshold.';
        }

        // Rule 3: New receiver + high amount.
        $hasPreviousTransfer =
            Transfer::where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->exists();

        if (
            !$hasPreviousTransfer &&
            $amount > 50000
        ) {
            $reasons[] =
                'High-value transfer to a new receiver.';
        }

        /*
         * Rule 4:
         * Account created less than 10 minutes ago
         * and immediately transferring money.
         */
        if (
            $sender->created_at &&
            $sender->created_at->greaterThan(
                now()->subMinutes(10)
            )
        ) {
            $reasons[] =
                'New account attempting an immediate transfer.';
        }

        //  OTP requirement.
        $otpRequired =
            $amount > 50000 ||
            collect($reasons)->contains(
                fn($reason) =>
                str_contains(
                    strtolower($reason),
                    'new receiver'
                )
            ) ||
            collect($reasons)->contains(
                fn($reason) =>
                str_contains(
                    strtolower($reason),
                    'new account'
                )
            );

        return [
            'flagged' => !empty($reasons),
            'otp_required' => $otpRequired,
            'reasons' => $reasons,
        ];
    }

    // Scan Completed Transaction

    public function scanTransaction(Transfer $transfer): void
    {
        $transfer->loadMissing(['sender', 'receiver']);

        $result = $this->checkTransfer(
            sender: $transfer->sender,
            receiver: $transfer->receiver,
            amount: $transfer->amount
        );

        if (!$result['flagged']) {
            return;
        }

        foreach ($result['reasons'] as $reason) {
            $fraudRule = FraudRule::where(
                'name',
                'like',
                '%' . $reason . '%'
            )->first();

            FraudReport::create([
                'user_id' => $transfer->sender_id,
                'transaction_id' => $transfer->id,
                'fraud_rule_id' => $fraudRule?->id,
                'status' => 'open',
                'description' => $reason,
            ]);
        }
    }

    // Investigate Fraud
    public function investigate(
        FraudReport $report,
        User $admin,
        ?string $notes = null
    ): FraudReport {
        if (!$admin->hasRole('admin')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'admin' => 'Only admins can investigate fraud reports.',
            ]);
        }

        $oldStatus = $report->status;

        $report->update([
            'status' => 'investigating',
            'investigated_by' => $admin->id,
            'investigation_notes' => $notes,
            'investigated_at' => now(),
        ]);

        //Audit.
        app(AuditLogService::class)->log(
            actor: $admin,
            action: 'fraud.investigated',
            description: 'Admin started fraud investigation',
            model: $report,
            oldValues: [
                'status' => $oldStatus,
            ],
            newValues: [
                'status' => 'investigating',
                'investigated_by' => $admin->id,
                'notes' => $notes,
            ]
        );

        return $report->fresh();
    }

    // Resolve Fraud
    public function resolve(
        FraudReport $report,
        User $admin,
        string $resolution
    ): FraudReport {
        if (!$admin->hasRole('admin')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'admin' => 'Only admins can resolve fraud reports.',
            ]);
        }

        $oldStatus = $report->status;

        $report->update([
            'status' => 'resolved',
            'resolved_by' => $admin->id,
            'resolution' => $resolution,
            'resolved_at' => now(),
        ]);

        // Audit.
        app(AuditLogService::class)->log(
            actor: $admin,
            action: 'fraud.resolved',
            description: 'Admin resolved fraud investigation',
            model: $report,
            oldValues: [
                'status' => $oldStatus,
            ],
            newValues: [
                'status' => 'resolved',
                'resolved_by' => $admin->id,
                'resolution' => $resolution,
            ]
        );

        return $report->fresh();
    }
}
