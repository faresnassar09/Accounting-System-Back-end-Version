<?php

namespace Modules\Accounting\Services\Logging;

use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Models\AccountingAuditLog;

class AuditLogService
{
    /**
     * Record an immutable audit log event.
     */
    public function record(
        string $event,
        Model $model,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AccountingAuditLog {
        $user = auth('admin')->user() ?? auth('api')->user() ?? auth()->user();

        return AccountingAuditLog::create([
            'auditable_type' => get_class($model),
            'auditable_id'   => (int) $model->getKey(),
            'event'          => $event,
            'user_id'        => $user?->getAuthIdentifier(),
            'user_type'      => $user ? class_basename($user) : 'System',
            'user_name'      => $user?->name ?? $user?->email ?? 'System / Background Job',
            'ip_address'     => request()?->ip() ?? '127.0.0.1',
            'description'    => $description,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'created_at'     => now(),
        ]);
    }
}
