<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'created_by'];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (!$m->slug) $m->slug = Str::slug($m->name);
        });
        static::updating(function ($m) {
            if ($m->isDirty('name') && !$m->isDirty('slug')) {
                $m->slug = Str::slug($m->name);
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
    
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'category_courses');
    }
}
