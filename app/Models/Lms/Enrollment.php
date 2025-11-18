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
        'completed_at'
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
    public function latestPretestAttempt()
    {
        return $this->hasOne(QuizAttempt::class, 'user_id', 'user_id')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('quizzes.quizzable_type', Course::class)
            // Gunakan $this->course_id untuk memastikan join ke course yang benar
            ->where('quizzes.quizzable_id', $this->course_id)
            ->where('quizzes.quiz_kind', Quiz::KIND_PRETEST)
            ->select('quiz_attempts.*') // Hindari konflik kolom 'id'
            ->latest('quiz_attempts.created_at'); // Ambil yang terbaru
    }

    /**
     * TAMBAHAN: Relasi untuk mendapatkan attempt Post-test TERAKHIR
     * dari user ini untuk course ini.
     */
    public function latestPosttestAttempt()
    {
        return $this->hasOne(QuizAttempt::class, 'user_id', 'user_id')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('quizzes.quizzable_type', Course::class)
            ->where('quizzes.quizzable_id', $this->course_id)
            ->where('quizzes.quiz_kind', Quiz::KIND_POSTTEST)
            ->select('quiz_attempts.*')
            ->latest('quiz_attempts.created_at');
    }
}
