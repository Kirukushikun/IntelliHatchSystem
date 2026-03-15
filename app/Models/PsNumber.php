<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsNumber extends Model
{
    use HasFactory;

    protected $table = 'ps-numbers';

    protected $fillable = [
        'psNumber',
        'isActive',
        'creationDate',
    ];

    protected $casts = [
        'isActive' => 'boolean',
        'creationDate' => 'datetime',
    ];

    protected $dates = [
        'creationDate',
    ];
}
