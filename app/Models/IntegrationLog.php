<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $fillable = [
        'admin_id',
        'source',
        'action',
        'external_id',
        'status',
        'message',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
