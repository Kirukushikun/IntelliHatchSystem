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
        'form_type_ids',
        'context_period',
        'context_date_from',
        'context_date_to',
        'status',
        'response',
        'error_message',
    ];

    protected $casts = [
        'context_date_from' => 'date',
        'context_date_to' => 'date',
        'form_type_ids' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formType(): BelongsTo
    {
        return $this->belongsTo(FormType::class);
    }

    public function formScopeLabel(): string
    {
        // New multi-select field takes precedence
        $ids = $this->form_type_ids ?? [];

        if (! empty($ids)) {
            $names = FormType::whereIn('id', $ids)->orderBy('form_name')->pluck('form_name');

            return $names->isNotEmpty() ? $names->implode(', ') : 'All Form Types';
        }

        // Fallback for legacy records with single form_type_id
        return $this->formType ? $this->formType->form_name : 'All Form Types';
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
        if ($this->context_period === 'custom' && $this->context_date_from && $this->context_date_to) {
            return $this->context_date_from->format('M d, Y').' – '.$this->context_date_to->format('M d, Y');
        }

        return match ($this->context_period) {
            'week' => 'Current Week',
            'month' => 'Current Month',
            'all' => 'Last 90 Days',
            default => $this->context_period,
        };
    }
}
