<?php

use App\Services\ActivityLogger;

if (! function_exists('audit')) {
    /**
     * Log an activity with standardized module/action format.
     *
     * @param  string  $module   Where it happened (e.g. 'User', 'Hatcher', 'Form')
     * @param  string  $action   What happened (e.g. 'create', 'update', 'delete', 'login')
     * @param  string  $label    Human-readable description
     * @param  \Illuminate\Database\Eloquent\Model|null  $subject  The related model instance
     * @param  array   $meta     Optional extra data
     */
    function audit(string $module, string $action, string $label, mixed $subject = null, array $meta = []): void
    {
        ActivityLogger::log(
            action: $action,
            description: $label,
            module: $module,
            subjectId: $subject?->id ? (int) $subject->id : null,
            properties: $meta,
        );
    }
}
