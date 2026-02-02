<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function formType()
    {
        return $this->belongsTo(\App\Models\FormType::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
