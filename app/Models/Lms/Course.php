<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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
        'created_by',
        'using_due_date',
        'learning_objectives', // TAMBAHAN
    ];

    protected $casts = [
        'has_pretest' => 'boolean',
        'has_posttest' => 'boolean',
        'require_pretest_before_content' => 'boolean',
        'default_passing_score' => 'float',
        'pretest_passing_score' => 'float',
        'posttest_passing_score' => 'float',
        'learning_objectives' => 'array', // TAMBAHAN
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    // ... (relasi yang ada: modules, lessons, categories, tags, instructor) ...

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order'); // Tambahkan orderBy
    }

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

    /**
     * TAMBAHAN: Relasi untuk semua kuis yang terkait dengan course ini
     * (termasuk pretest dan posttest).
     */
    public function quizzes()
    {
        return $this->morphMany(Quiz::class, 'quizzable');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
