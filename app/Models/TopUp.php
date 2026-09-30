<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'method',
        'reference',
        'external_reference',
        'idempotency_key',
        'status',
        'processed_by',
        'failure_reason',
        'failed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * User receiving the top-up.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Wallet being credited.
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Agent who processed cash top-up.
     */
    public function processor()
    {
        return $this->belongsTo(
            User::class,
            'processed_by'
        );
    }

    /**
     * Related ledger transaction.
     */
    public function walletTransactions()
    {
        return $this->morphMany(
            WalletTransaction::class,
            'transactionable'
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
