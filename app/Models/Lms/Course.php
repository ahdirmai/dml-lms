<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Course extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'status',
        'difficulty',
        'has_pretest',
        'has_posttest',
        'default_passing_score',
        'pretest_passing_score',
        'posttest_passing_score',
        'require_pretest_before_content',
        'thumbnail_path',
        'instructor_id',
        'created_by'
    ];

    protected $casts = [
        'has_pretest' => 'boolean',
        'has_posttest' => 'boolean',
        'require_pretest_before_content' => 'boolean',
        'default_passing_score' => 'float',
        'pretest_passing_score' => 'float',
        'posttest_passing_score' => 'float',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    // Relations
    public function modules()
    {
        return $this->hasMany(Module::class);
    } // kalau ada
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_courses');
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'course_tags');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }

    // Pre/Post via polymorph
    public function pretest()
    {
        return $this->morphOne(Quiz::class, 'quizzable')->where('quiz_kind', 'pretest');
    }
    public function posttest()
    {
        return $this->morphOne(Quiz::class, 'quizzable')->where('quiz_kind', 'posttest');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
