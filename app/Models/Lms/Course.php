<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    /** ✅ UUID primary key */
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'thumbnail_url',
        'language',
        'level',
        'visibility',
        'status',
        'published_at',
        'created_by',
        'duration_minutes',
        'lessons_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = \Illuminate\Support\Str::slug($model->title) . '-' . \Illuminate\Support\Str::random(6);
            }
        });
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function modules()
    {
        return $this->hasMany(CourseModule::class)->orderBy('position');
    }

    public function lessons()
    {
        return $this->hasManyThrough(CourseLesson::class, CourseModule::class, 'course_id', 'module_id')
            ->orderBy('position');
    }

    public function categories()
    {
        return $this->belongsToMany(\App\Models\Lms\Category::class, 'category_course');
    }

    public function tags()
    {
        return $this->belongsToMany(\App\Models\Lms\Tag::class, 'course_tag');
    }
}
