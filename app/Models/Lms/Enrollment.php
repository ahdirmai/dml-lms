<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'enrolled_at',
        'completed_at',
    ];

    // Tambahkan cast untuk tanggal
    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * PERBAIKAN: Mengganti 'hadOne' (typo) menjadi 'hasOne'
     * dan mengubah nama relasi agar lebih jelas.
     */
    public function dueDate()
    {
        return $this->hasOne(EnrollmentDueDate::class, 'enrollment_id');
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class, 'enrollment_id');
    }

    /**
     * TAMBAHAN: Relasi untuk mendapatkan attempt Pre-test TERAKHIR
     * dari user ini untuk course ini.
     */
    public function getLatestPretestAttemptAttribute()
    {
        $pretest_id = $this->course->pretest->id;

        return QuizAttempt::where('user_id', $this->user_id)
            ->where('quiz_id', $pretest_id)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * TAMBAHAN: Relasi untuk mendapatkan attempt Post-test TERAKHIR
     * dari user ini untuk course ini.
     */
    public function getLatestPosttestAttemptAttribute()
    {
        $posttest_id = $this->course->posttest->id;

        return QuizAttempt::where('user_id', $this->user_id)
            ->where('quiz_id', $posttest_id)
            ->orderByDesc('created_at')
            ->first();
    }
}
