<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = [
        'enrollment_id',
        'lesson_id',
        'status',
        'started_at',
        'completed_at',
        'last_activity_at',
        'duration_seconds',
    ];

    protected $casts = [
        'started_at'      => 'datetime',
        'completed_at'    => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson()
    {
        return $this->belongsTo(\App\Models\Lms\Lesson::class);
    }

    // Scopes
    public function scopeCompleted(Builder $q): Builder
    {
        return $q->where('status', 'completed');
    }

    public function scopeInProgress(Builder $q): Builder
    {
        return $q->where('status', 'in_progress');
    }

    public function scopeNotStarted(Builder $q): Builder
    {
        return $q->where('status', 'not_started');
    }
}
