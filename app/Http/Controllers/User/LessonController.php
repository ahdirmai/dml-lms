<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Lesson;
use App\Models\Lms\LessonProgress;
use App\Models\Lms\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    /**
     * Menampilkan satu pelajaran (lesson) untuk user yang sedang login.
     * Route: Route::get('/lessons/{lessonId}', [LessonController::class, 'show'])->name('user.lessons.show');
     */
    public function show(Request $request, string $lessonId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Ambil Objek Inti
        // Eager load relasi quiz, pertanyaan, dan pilihan jawaban
        $lesson = Lesson::with([
            'quiz.questions.choices'
        ])->findOrFail($lessonId);

        // Ambil course induk
        $course = Course::findOrFail($lesson->course_id);

        // Otorisasi: Gagal (403) jika user tidak terdaftar
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        // 2. Siapkan Data Sidebar (Modul & Progres)
        $completedLessonIds = LessonProgress::where('enrollment_id', $enrollment->id)
            ->completed()
            ->pluck('lesson_id')
            ->flip();

        $all_modules = Module::where('course_id', $course->id)
            ->with(['lessons' => fn($q) => $q->orderBy('order_no', 'asc')])
            ->orderBy('order', 'asc')
            ->get();

        $modules = $all_modules->map(function ($module) use ($completedLessonIds) {
            $lessons = $module->lessons->map(function ($lesson) use ($completedLessonIds) {
                return [
                    'id'      => $lesson->id,
                    'title'   => $lesson->title,
                    'type'    => $lesson->kind,
                    'is_done' => isset($completedLessonIds[$lesson->id]),
                ];
            });
            return [
                'id'      => $module->id,
                'title'   => $module->title,
                'lessons' => $lessons->all(),
            ];
        });

        // 3. Tambahkan Properti 'meta' dinamis ke $lesson
        // (Blade membutuhkannya untuk subjudul)
        $meta = null;
        if ($lesson->kind === 'quiz' && $lesson->quiz) {
            $count = $lesson->quiz->questions->count();
            $meta = "$count pertanyaan";
        } elseif ($lesson->duration_minutes) {
            $meta = $lesson->duration_minutes . ' menit';
        }
        $lesson->meta = $meta; // Tambahkan properti ini ke objek Eloquent

        // 4. Kirim ke View (TANPA $content)
        // Blade akan membaca properti langsung dari $lesson
        return view('user.lessons.show', compact(
            'course',
            'modules',
            'lesson'
        ));
    }
}
