<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CourseModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'course_modules';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['course_id', 'title', 'description', 'position', 'is_published'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(CourseLesson::class, 'module_id')->orderBy('position');
    }
}
