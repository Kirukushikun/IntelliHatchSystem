<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormType extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_name',
    ];

    public function forms()
    {
        return $this->hasMany(Form::class);
    }
}
