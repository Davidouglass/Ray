<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AdminAudit
{
    public static function log(string $action, ?Model $target = null, array $meta = []): void
    {
        try {
            $admin = Auth::guard('admin')->user();

            AdminAuditLog::create([
                'admin_id'    => $admin?->id,
                'action'      => $action,
                'target_type' => $target ? class_basename($target) : null,
                'target_id'   => $target?->getKey(),
                'meta'        => $meta ?: null,
                'ip'          => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // Auditing must never break an admin action.
        }
    }

    public static function describe(AdminAuditLog $log): string
    {
        return str_replace(['.', '_'], [' · ', ' '], $log->action);
    }
}
