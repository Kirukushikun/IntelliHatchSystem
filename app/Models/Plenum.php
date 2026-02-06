<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plenum extends Model
{
    use HasFactory;

    protected $table = 'plenum-machines';

    protected $fillable = [
        'plenumName',
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
