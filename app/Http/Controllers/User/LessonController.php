<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
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
        // return 'x';
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        // 1. Ambil Objek Inti
        // Eager load relasi quiz, pertanyaan, dan pilihan jawaban
        $lesson = Lesson::with([
            'quiz.questions.choices',
        ])->findOrFail($lessonId);

        // Ambil course induk
        $course = Course::findOrFail($lesson->course_id);

        // Otorisasi: Gagal (403) jika user tidak terdaftar
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->with('dueDate')
            ->firstOrFail();

        // Check Due Date Access
        if ($course->using_due_date) {
            $dueDate = $enrollment->dueDate;
            $now = now();

            if ($dueDate) {
                if ($dueDate->start_date && $now->lt($dueDate->start_date)) {
                    return redirect()->route('user.courses.show', $course->id)
                        ->with('error', 'Kursus belum dimulai. Akses dibuka pada ' . \Carbon\Carbon::parse($dueDate->start_date)->format('d M Y'));
                } elseif ($dueDate->end_date && $now->gt($dueDate->end_date)) {
                    return redirect()->route('user.courses.show', $course->id)
                        ->with('error', 'Masa akses kursus telah berakhir pada ' . \Carbon\Carbon::parse($dueDate->end_date)->format('d M Y'));
                }
            }
        }

        // 2. Siapkan Data Sidebar (Modul & Progres)
        $completedLessonIds = LessonProgress::where('enrollment_id', $enrollment->id)
            ->completed()
            ->pluck('lesson_id')
            ->flip();

        $all_modules = Module::where('course_id', $course->id)
            ->with(['lessons' => fn ($q) => $q->orderBy('order_no', 'asc')])
            ->orderBy('order', 'asc')
            ->get();

        $modules = $all_modules->map(function ($module) use ($completedLessonIds) {
            $allPreviousCompleted = true; // Track if all previous lessons are completed
            
            $lessons = $module->lessons->map(function ($lesson) use ($completedLessonIds, &$allPreviousCompleted) {
                $isDone = isset($completedLessonIds[$lesson->id]);
                $isAccessible = $allPreviousCompleted; // Can access if all previous are done
                
                // Update flag for next iteration
                if (!$isDone) {
                    $allPreviousCompleted = false;
                }
                
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'type' => $lesson->kind,
                    'is_done' => $isDone,
                    'is_accessible' => $isAccessible,
                ];
            });

            return [
                'id' => $module->id,
                'title' => $module->title,
                'lessons' => $lessons->all(),
            ];
        });

        // 3. Tambahkan Properti 'meta' dinamis ke $lesson
        // (Blade membutuhkannya untuk subjudul)
        $meta = null;
        if ($lesson->kind === 'quiz' && $lesson->quiz) {
            $count = $lesson->quiz->questions->count();
            $meta = "$count pertanyaan";
        } elseif ($lesson->duration_seconds) {
            $meta = convert_seconds_to_duration($lesson->duration_seconds);
        }
        $lesson->meta = $meta; // Tambahkan properti ini ke objek Eloquent

        // 4. Ambil Progress Saat Ini (untuk resume video & tracking time)
        $currentProgress = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        // 5. Kirim ke View (TANPA $content)
        // Blade akan membaca properti langsung dari $lesson
        return view('user.lessons.show', compact(
            'course',
            'modules',
            'lesson',
            'currentProgress'
        ));
    }

    public function updateProgress(Request $request, string $lessonId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $lesson = Lesson::findOrFail($lessonId);
        $course = Course::findOrFail($lesson->course_id);
        
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        $progress = LessonProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => 'not_started',
                'duration_seconds' => 0,
                'last_watched_second' => 0,
            ]
        );

        if ($progress->status === 'not_started') {
            $progress->status = 'in_progress';
            $progress->started_at = now();
        }

        // Update last activity
        $progress->last_activity_at = now();

        // Update duration (accumulate time spent)
        // Client sends 'add_seconds' (e.g. 60)
        if ($request->has('add_seconds')) {
            $add = (int) $request->add_seconds;
            // Basic validation to prevent spamming huge numbers
            if ($add > 0 && $add <= 120) { 
                $progress->duration_seconds += $add;
            }
        }

        // Update last watched position (for video)
        if ($request->has('last_watched_second')) {
            $progress->last_watched_second = (int) $request->last_watched_second;
        }

        $progress->save();

        return response()->json([
            'status' => 'success',
            'progress' => $progress
        ]);
    }

    public function markAsComplete(Request $request, string $lessonId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $lesson = Lesson::findOrFail($lessonId);
        $course = Course::findOrFail($lesson->course_id);
        
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        $progress = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        // Security Check: Ensure user has spent enough time
        // Rule:
        // GDrive/Text: Time Spent >= Duration - 60s
        // Video: Time Spent >= Duration - 30s OR Last Watched >= Duration - 30s
        
        $isCompleteable = false;
        $duration = $lesson->duration_seconds ?? 0;
        $spent = $progress->duration_seconds;
        $lastWatched = $progress->last_watched_second;

        // If duration is 0 or null, assume it's always completeable (or handle as edge case)
        if ($duration <= 0) {
            $isCompleteable = true;
        } else {
            if ($lesson->kind === 'video' || $lesson->youtube_video_id) {
                // Video Logic
                // "button ... muncul ketika lama waktu user membuka page <30 detik dari lesson duration"
                // Interpretasi: Sisa waktu < 30 detik.
                $threshold = max(0, $duration - 30);
                if ($spent >= $threshold || $lastWatched >= $threshold) {
                    $isCompleteable = true;
                }
            } else {
                // GDrive / Text Logic
                // "user harus membuka page hingga 1 menit terakhir"
                $threshold = max(0, $duration - 60);
                if ($spent >= $threshold) {
                    $isCompleteable = true;
                }
            }
        }

        if (!$isCompleteable) {
            return response()->json(['message' => 'Belum memenuhi syarat waktu minimum.'], 403);
        }

        $progress->status = 'completed';
        $progress->completed_at = now();
        $progress->save();

        return response()->json([
            'status' => 'success',
            'redirect' => route('user.lessons.show', $lesson->id) // Refresh or Next
        ]);
    }
}
