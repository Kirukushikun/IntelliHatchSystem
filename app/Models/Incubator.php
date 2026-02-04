<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incubator extends Model
{
    use HasFactory;

    protected $fillable = [
        'incubatorName',
        'isDisabled',
        'creationDate',
    ];

    protected $casts = [
        'isDisabled' => 'boolean',
        'creationDate' => 'datetime',
    ];

    protected $dates = [
        'creationDate',
    ];
}
