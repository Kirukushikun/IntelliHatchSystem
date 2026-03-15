<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseNumber extends Model
{
    use HasFactory;

    protected $table = 'house-numbers';

    protected $fillable = [
        'houseNumber',
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
