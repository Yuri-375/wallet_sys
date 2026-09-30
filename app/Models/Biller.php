<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'description',
        'api_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Biller has many bill payments.
    public function billPayments()
    {
        return $this->hasMany(
            BillPayment::class
        );
    }

    /**
     * Check active status.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
