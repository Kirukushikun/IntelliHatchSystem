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
    ];

    /**
     * Get the form type that owns the form.
     */
    public function formType(): BelongsTo
    {
        return $this->belongsTo(FormType::class);
    }

    /**
     * Get the user who uploaded the form.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
