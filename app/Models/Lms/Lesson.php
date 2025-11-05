<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Lesson extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'course_id',
        'module_id',
        'description',
        'title',
        'kind',
        'content_url',
        'youtube_video_id',
        'gdrive_file_id',
        'order'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    } // only if kind=quiz
}
