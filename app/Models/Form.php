<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_type_id',
        'form_inputs',
        'date_submitted',
        'uploaded_by',
    ];

    protected $casts = [
        'form_inputs' => 'array',
        'date_submitted' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function formType(): BelongsTo
    {
        return $this->belongsTo(FormType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function incubator(): BelongsTo
    {
        return $this->belongsTo(Incubator::class, 'incubator_id');
    }
}
