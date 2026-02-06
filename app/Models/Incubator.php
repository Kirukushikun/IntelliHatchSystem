<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incubator extends Model
{
    use HasFactory;

    protected $table = 'incubator-machines';

    protected $fillable = [
        'incubatorName',
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
