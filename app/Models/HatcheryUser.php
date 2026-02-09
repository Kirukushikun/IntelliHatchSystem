<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HatcheryUser extends Model
{
    protected $table = 'hatchery_users';
    
    protected $fillable = [
        'first_name',
        'last_name',
        'is_disabled',
    ];

    protected $casts = [
        'is_disabled' => 'boolean',
    ];
}
