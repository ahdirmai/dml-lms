<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

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

    // relasi ke Course nanti: hasMany(Course::class)
}
