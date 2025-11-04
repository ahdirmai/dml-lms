<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Course extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'slug',
        'subtitle',
        'description',
        'thumbnail_path',
        'difficulty',
        'status',
        'published_at',
        'instructor_id'
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_course');
    }
    public function instructor()
    {
        return $this->belongsTo(\App\Models\User::class, 'instructor_id');
    }
    public function modules()
    {
        return $this->hasMany(Module::class);
    }
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
