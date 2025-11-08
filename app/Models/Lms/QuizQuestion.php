<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuizQuestion extends Model
{
    use HasUuids;

    protected $fillable = ['quiz_id', 'question_text', 'question_type', 'order_no', 'score'];
    protected $casts = ['score' => 'float'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id');
    }
}
