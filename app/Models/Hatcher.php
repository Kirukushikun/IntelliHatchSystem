<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hatcher extends Model
{
    use HasFactory;

    protected $table = 'hatcher-machines';

    protected $fillable = [
        'hatcherName',
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
