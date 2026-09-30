<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency',
        'balance',
        'is_frozen',
        'frozen_at',
        'freeze_reason',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'is_frozen' => 'boolean',
            'frozen_at' => 'datetime',
        ];
    }

    /**
     * Wallet belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Wallet has many transactions.
     */
    public function transactions()
    {
        return $this->hasMany(
            WalletTransaction::class
        );
    }

    /**
     * Check if wallet is frozen.
     */
    public function isFrozen(): bool
    {
        return $this->is_frozen;
    }

    /**
     * Check if wallet is active.
     */
    public function isActive(): bool
    {
        return ! $this->is_frozen;
    }
}
