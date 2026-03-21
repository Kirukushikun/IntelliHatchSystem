<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        string $action,
        string $description,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $properties = []
    ): void {
        try {
            ActivityLog::create([
                'user_id'      => Auth::id(),
                'action'       => $action,
                'description'  => $description,
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
                'properties'   => empty($properties) ? null : $properties,
                'ip_address'   => request()->ip(),
            ]);
        } catch (\Throwable) {
            // Never let logging break the application
        }
    }
}
