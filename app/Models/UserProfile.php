<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'department',
        'job_title',
        'raw_payload',
        'is_hr',
        'is_employee'
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // // Relasi manager (berdasarkan external_id)
    // public function manager()
    // {
    //     return $this->belongsTo(User::class, 'manager_external_id', 'external_id');
    // }

    // public function directReports()
    // {
    //     return $this->hasMany(UserProfile::class, 'manager_external_id', 'user.external_id');
    //     // Kalau mau lebih rapi, nanti bisa disesuaikan lagi; ini sekadar sketsa
    // }
}
