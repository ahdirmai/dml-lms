<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedSsoToken extends Model
{
    protected $fillable = [
        'jti',
        'used_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];
}
