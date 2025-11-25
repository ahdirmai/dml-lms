<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuizAttempt extends Model
{
    use HasUuids;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'attempt_no',
        'started_at',
        'finished_at',
        'score',
        'passed',
        'duration_seconds'
    ];

    protected $casts = [
        'score' => 'float',
        'passed' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_seconds' => 'integer'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }
}
