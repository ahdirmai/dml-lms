<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuizAnswer extends Model
{
    use HasUuids;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option_id',
        'answer_text',
        'is_correct',
        'score_awarded'
    ];
    protected $casts = ['is_correct' => 'boolean', 'score_awarded' => 'float'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
    public function selectedOption()
    {
        return $this->belongsTo(QuizOption::class, 'selected_option_id');
    }
}
