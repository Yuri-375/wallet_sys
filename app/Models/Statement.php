<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'period_start',
        'period_end',
        'opening_balance',
        'total_credits',
        'total_debits',
        'closing_balance',
        'pdf_path',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'opening_balance' => 'integer',
            'total_credits' => 'integer',
            'total_debits' => 'integer',
            'closing_balance' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * Statement belongs to user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Statement belongs to wallet.
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Check generated status.
     */
    public function isGenerated(): bool
    {
        return $this->status === 'generated';
    }
}
