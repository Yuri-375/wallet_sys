<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'currency',
        'reference',
        'idempotency_key',
        'status',
        'otp_code',
        'otp_hash',
        'otp_expires_at',
        'otp_attempts',
        'otp_verified_at',
        'description',
    ];
    protected function casts(): array
    {
        return [
            'otp_expires_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
    // Sender

    public function sender()
    {
        return $this->belongsTo(
            User::class,
            'sender_id'
        );
    }

    // Receiver
    public function receiver()
    {
        return $this->belongsTo(
            User::class,
            'receiver_id'
        );
    }

    // Wallet Transaction

    public function walletTransactions()
    {
        return $this->morphMany(
            WalletTransaction::class,
            'transactionable'
        );
    }

    public function requiresOtp(): bool
    {
        return $this->status === 'otp_required';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOtpExpired(): bool
    {
        return ! $this->otp_expires_at
            || $this->otp_expires_at->isPast();
    }
}
