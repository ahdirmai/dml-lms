<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuizOption extends Model
{
    use HasUuids;

    protected $fillable = ['question_id', 'option_text', 'is_correct', 'order_no'];
    protected $casts = ['is_correct' => 'boolean'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
