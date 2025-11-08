<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Quiz extends Model
{
    use HasUuids;

    // Jenis quiz yang kita dukung
    public const KIND_PRETEST  = 'pretest';
    public const KIND_POSTTEST = 'posttest';
    public const KIND_REGULAR  = 'regular';

    protected $table = 'quizzes';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'time_limit_seconds',
        'shuffle_questions',
        'shuffle_options',
        'passing_score',
        'quiz_kind',           // pretest|posttest|regular
        'quizzable_type',      // Course::class | Lesson::class
        'quizzable_id',
    ];

    protected $casts = [
        'time_limit_seconds' => 'integer',
        'shuffle_questions'  => 'boolean',
        'shuffle_options'    => 'boolean',
        'passing_score'      => 'float',
    ];

    // ==== RELATIONS ====
    public function quizzable()
    {
        return $this->morphTo();
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // ==== ACCESSORS / HELPERS ====

    /**
     * Passing score efektif:
     * 1) pakai passing_score di quiz jika ada
     * 2) kalau quizzable Course:
     *    - pretest => course.pretest_passing_score
     *    - posttest => course.posttest_passing_score
     *    - selain itu => course.default_passing_score
     * 3) fallback ke config('lms.default_passing_score', 70)
     */
    public function getEffectivePassingScoreAttribute(): float
    {
        if (!is_null($this->passing_score)) {
            return (float) $this->passing_score;
        }

        if ($this->quizzable instanceof Course) {
            if ($this->quiz_kind === self::KIND_PRETEST && !is_null($this->quizzable->pretest_passing_score)) {
                return (float) $this->quizzable->pretest_passing_score;
            }
            if ($this->quiz_kind === self::KIND_POSTTEST && !is_null($this->quizzable->posttest_passing_score)) {
                return (float) $this->quizzable->posttest_passing_score;
            }
            if (!is_null($this->quizzable->default_passing_score)) {
                return (float) $this->quizzable->default_passing_score;
            }
        }

        return (float) config('lms.default_passing_score', 70);
    }

    public function isPretest(): bool
    {
        return $this->quiz_kind === self::KIND_PRETEST;
    }

    public function isPosttest(): bool
    {
        return $this->quiz_kind === self::KIND_POSTTEST;
    }

    public function isRegular(): bool
    {
        return $this->quiz_kind === self::KIND_REGULAR;
    }

    // ==== SCOPES ====
    public function scopePretest($q)
    {
        return $q->where('quiz_kind', self::KIND_PRETEST);
    }

    public function scopePosttest($q)
    {
        return $q->where('quiz_kind', self::KIND_POSTTEST);
    }

    public function scopeRegular($q)
    {
        return $q->where('quiz_kind', self::KIND_REGULAR);
    }

    public function scopeForCourse($q, string $courseId)
    {
        return $q->where('quizzable_type', Course::class)
            ->where('quizzable_id', $courseId);
    }

    public function scopeForLesson($q, string $lessonId)
    {
        return $q->where('quizzable_type', Lesson::class)
            ->where('quizzable_id', $lessonId);
    }
}
