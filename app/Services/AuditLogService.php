<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * Create an audit log.
     */
    public function log(
        ?User $actor,
        string $action,
        ?string $description = null,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'description' => $description,

            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),

            'old_values' => $oldValues,
            'new_values' => $newValues,

            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
