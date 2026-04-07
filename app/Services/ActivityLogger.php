<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Standardized action names for consistency.
     */
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_ENABLE = 'enable';
    public const ACTION_DISABLE = 'disable';
    public const ACTION_IMPORT = 'import';
    public const ACTION_EXPORT = 'export';
    public const ACTION_RESET_PASSWORD = 'reset_password';
    public const ACTION_CHANGE_PASSWORD = 'change_password';

    public static function log(
        string $action,
        string $description,
        ?string $module = null,
        ?int $subjectId = null,
        array $properties = [],
        // Legacy alias for module — kept for backward compatibility
        ?string $subjectType = null,
    ): void {
        try {
            ActivityLog::create([
                'user_id'      => Auth::id(),
                'action'       => $action,
                'description'  => $description,
                'subject_type' => $module ?? $subjectType,
                'subject_id'   => $subjectId,
                'properties'   => empty($properties) ? null : $properties,
                'ip_address'   => request()->ip(),
            ]);
        } catch (\Throwable) {
            // Never let logging break the application
        }
    }
}
