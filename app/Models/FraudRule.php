<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraudRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'threshold',
        'configuration',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'integer',
            'configuration' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Fraud rule has many reports.
     */
    public function fraudReports()
    {
        return $this->hasMany(
            FraudReport::class
        );
    }

    /**
     * Check whether rule is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
