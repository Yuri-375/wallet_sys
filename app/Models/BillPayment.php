<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'biller_id',
        'amount',
        'account_number',
        'reference',
        'idempotency_key',
        'status',
        'external_reference',
        'failure_reason',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * User who made the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Biller.
     */
    public function biller()
    {
        return $this->belongsTo(Biller::class);
    }

    /**
     * Related ledger transactions.
     */
    public function walletTransactions()
    {
        return $this->morphMany(
            WalletTransaction::class,
            'transactionable'
        );
    }

    /**
     * Check completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
