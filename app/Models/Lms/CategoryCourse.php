<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CategoryCourse extends Model
{
    use HasUuids;

    protected $table = 'category_courses';
    public $timestamps = false; // pivot tidak pakai timestamps
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['category_id', 'course_id'];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relasi ke Course
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
