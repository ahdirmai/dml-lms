<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasUuids;

    protected $fillable = ['course_id', 'title', 'slug', 'kind', 'content', 'order_no', 'duration_seconds', 'module_id', 'description', 'youtube_video_id', 'gdrive_file_id', 'content_url'];

    protected $casts = ['duration_seconds' => 'integer'];

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

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
