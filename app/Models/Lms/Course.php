<?php

namespace App\Models\Lms;

use App\Models\User;
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
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_id');
    }

    public function students()
    {
        // convenience relation
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'user_id')
            ->withPivot(['status', 'enrolled_at', 'completed_at'])
            ->withTimestamps();
    }
}
