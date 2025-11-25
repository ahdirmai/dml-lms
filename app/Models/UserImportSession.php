<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImportSession extends Model
{
    protected $fillable = [
        'admin_id',
        'session_token',
        'filters',
        'payload',
        'total_records',
        'expires_at',
    ];

    protected $casts = [
        'filters'     => 'array',
        'payload'     => 'array',
        'expires_at'  => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
