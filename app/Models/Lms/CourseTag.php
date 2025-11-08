<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CourseTag extends Model
{
    use HasUuids;

    protected $table = 'course_tags';
    public $timestamps = false; // pivot table biasanya tidak punya timestamps
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'course_id',
        'tag_id',
    ];

    // Relasi ke Course
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // Relasi ke Tag
    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }
}
