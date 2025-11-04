<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Quiz extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'lesson_id', 'title', 'time_limit_seconds', 'shuffle_questions'];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }
}
