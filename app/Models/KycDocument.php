<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_path',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    /**
     * KYC document belongs to the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Agent who verified the document.
     */
    public function verifier()
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    /**
     * Check whether approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check whether pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check whether rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
