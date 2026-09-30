<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'user.registered' => 'Register Account',

            'login.success' => 'Login',
            'login.failed' => 'Failed Login',
            'logout' => 'Logout',

            'kyc.submitted' => 'Submit KYC',
            'kyc.approved' => 'Approve KYC',
            'kyc.rejected' => 'Reject KYC',

            'wallet.created' => 'Create Wallet',
            'wallet.credited' => 'Credit Wallet',
            'wallet.debited' => 'Debit Wallet',
            'wallet.frozen' => 'Freeze Wallet',
            'wallet.unfrozen' => 'Unfreeze Wallet',

            'topup.completed' => 'Cash Top-up',

            'transfer.otp_required' => 'Transfer Requires OTP',
            'transfer.otp_failed' => 'Transfer OTP Failed',
            'transfer.completed' => 'Transfer Money',

            'bill.created' => 'Create Bill Payment',
            'bill.paid' => 'Pay Bill',
            'bill.failed' => 'Failed Bill Payment',
            'bill.provider_uncertain' => 'Bill Provider Uncertain',

            'fraud.investigated' => 'Investigate Fraud',
            'fraud.resolved' => 'Resolve Fraud',

            default => ucwords(
                str_replace(['.', '_'], ' ', $this->action)
            ),
        };
    }
}
