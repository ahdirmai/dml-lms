<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Lesson extends Model
{
    use HasUuids;

    protected $fillable = ['course_id', 'title', 'slug', 'kind', 'content', 'order_no', 'duration_minutes'];
    protected $casts = ['duration_minutes' => 'integer'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function quiz()
    {
        return $this->morphOne(Quiz::class, 'quizzable')->where('quiz_kind', 'regular');
    }
}
