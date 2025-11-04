<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuizOption extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'question_id', 'option_text', 'is_correct'];

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
