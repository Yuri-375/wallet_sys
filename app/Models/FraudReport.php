<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraudReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'fraud_rule_id',
        'transaction_id',
        'severity',
        'status',
        'description',
        'metadata',
        'assigned_to',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * User associated with fraud report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Wallet associated with report.
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Fraud rule that triggered report.
     */
    public function fraudRule()
    {
        return $this->belongsTo(
            FraudRule::class
        );
    }

    /**
     * Related wallet transaction.
     */
    public function transaction()
    {
        return $this->belongsTo(
            WalletTransaction::class,
            'transaction_id'
        );
    }

    /**
     * Admin/agent assigned to report.
     */
    public function assignedUser()
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    /**
     * Check whether report is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check whether resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }
}
