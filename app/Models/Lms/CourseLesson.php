<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CourseLesson extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'module_id',
        'title',
        'content_type',
        'body',
        'video_url',
        'meta',
        'duration_minutes',
        'position',
        'is_preview',
        'is_published',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_preview' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
    public function module()
    {
        return $this->belongsTo(CourseModule::class);
    }

    public function course()
    {
        return $this->hasOneThrough(Course::class, CourseModule::class, 'id', 'id', 'module_id', 'course_id');
    }
}
