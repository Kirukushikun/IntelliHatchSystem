<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChat extends Model
{
    protected $fillable = [
        'user_id',
        'prompt',
        'system_prompt_snapshot',
        'context_data',
        'form_type_id',
        'context_period',
        'status',
        'response',
        'error_message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formType(): BelongsTo
    {
        return $this->belongsTo(FormType::class);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'analyzing']);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['done', 'failed']);
    }

    public function contextPeriodLabel(): string
    {
        return match ($this->context_period) {
            'week'  => 'Current Week',
            'month' => 'Current Month',
            'all'   => 'Last 90 Days',
            default => $this->context_period,
        };
    }
}
