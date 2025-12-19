<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\LessonProgress;
use App\Models\Lms\QuizAttempt;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseProgressController extends Controller
{
    /**
     * Tampilkan halaman Progress Tracking sebuah course.
     */
    public function show(Request $request, Course $course)
    {
        // Hanya pemilik course yang boleh akses
        abort_unless($course->instructor_id === Auth::id(), 403);

        // 1) Ambil enrollments & user
        $enrollments = Enrollment::query()
            ->where('course_id', $course->id)
            ->with(['user:id,name,email'])
            ->when($request->q, function ($q) use ($request) {
                $q->whereHas('user', function ($u) use ($request) {
                    $u->where('name', 'like', "%{$request->q}%")
                      ->orWhere('email', 'like', "%{$request->q}%");
                });
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->paginate(20);

        // 2) Total lessons course (semua modul)
        $totalLessons = $course->lessons()->count(); 

        // 3) Aggregate progress per enrollment (untuk halaman ini saja)
        // Kita ambil progress map untuk enrollments yang sedang ditampilkan (pagination)
        $progressMap = LessonProgress::query()
            ->selectRaw('enrollment_id, SUM(CASE WHEN status="completed" THEN 1 ELSE 0 END) AS completed, COUNT(*) AS total')
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->groupBy('enrollment_id')
            ->get()
            ->keyBy('enrollment_id');

        // 4) Bentuk list students
        $students = $enrollments->map(function ($en) use ($progressMap, $totalLessons) {
            $agg = $progressMap[$en->id] ?? null;
            $completed = (int) ($agg->completed ?? 0);
            // Jika total lesson di progress belum ada, pakai totalLessons course
            $total     = $totalLessons > 0 ? $totalLessons : 1; 
            
            $progress  = round(($completed / $total) * 100);
            
            // last activity: ambil max last_activity_at di lesson_progress enrollment tsb
            $lastActivityVal = LessonProgress::where('enrollment_id', $en->id)->max('last_activity_at');
            $lastActivity = $lastActivityVal 
                ? \Carbon\Carbon::parse($lastActivityVal)->format('Y-m-d H:i') 
                : '-';

            return [
                'name'              => $en->user->name,
                'email'             => $en->user->email,
                'status'            => $en->status, // assigned|active|completed|cancelled
                'progress'          => $progress,
                'completed_lessons' => $completed,
                'total_lessons'     => $total,
                'last_activity'     => $lastActivity,
            ];
        });

        // 5) Summary (Global, bukan paginated)
        // Hitung total enrollment global
        $allEnrollmentsCount = Enrollment::where('course_id', $course->id)->count();
        $activeCount = Enrollment::where('course_id', $course->id)->where('status', 'active')->count();
        $completedCount = Enrollment::where('course_id', $course->id)->where('status', 'completed')->count();
        
        // Avg progress global (agak berat kalau data banyak, bisa di-cache atau estimasi)
        // Untuk simpelnya, kita ambil rata-rata dari field 'progress' di tabel enrollments jika ada, 
        // atau hitung manual via join. Di sini kita skip avg global yang kompleks, 
        // atau kita hitung simple dari sample.
        $avgProgress = 0; // Placeholder atau implementasi query aggregate yang lebih efisien

        $summary = [
            'students_total'     => $allEnrollmentsCount,
            'students_active'    => $activeCount,
            'students_completed' => $completedCount,
            'avg_progress'       => $avgProgress, 
        ];

        // 6) Module breakdown
        $moduleBreakdown = $course->modules()
            ->withCount('lessons')
            ->get()
            ->map(function ($m) {
                // count completed lesson in this module across all students
                $completed = LessonProgress::whereIn(
                        'lesson_id',
                        $m->lessons()->pluck('id')
                    )->completed()->count();
                
                // Total lesson attempts possible = lessons_count * total_students
                // Tapi untuk display "Avg completed", kita bisa bagi dengan total students
                // Atau tampilkan total completed actions.
                // Sesuai UI: "Avg X / Y lessons" -> berarti rata-rata user menyelesaikan berapa lesson di modul ini.
                
                $totalStudents = Enrollment::where('course_id', $m->course_id)->count();
                $avgCompleted = $totalStudents > 0 ? round($completed / $totalStudents, 1) : 0;

                return [
                    'id' => $m->id,
                    'title' => $m->title,
                    'lessons_total' => $m->lessons_count,
                    'lessons_completed' => $avgCompleted, // Rata-rata
                ];
            })->toArray();

        return view('instructor.pages.courses.progress', compact(
            'course',
            'summary',
            'moduleBreakdown',
            'students',
            'enrollments' // untuk pagination links
        ));
    }

    /**
     * Show detailed progress for a specific student
     */
    public function showStudent(Course $course, User $student)
    {
        // Hanya pemilik course yang boleh akses
        abort_unless($course->instructor_id === Auth::id(), 403);

        // Get enrollment
        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('user_id', $student->id)
            ->with(['dueDate'])
            ->firstOrFail();

        // Get quiz attempts (Pretest & Posttest)
        $pretestAttempts = [];
        $posttestAttempts = [];

        if ($course->pretest) {
            $pretestAttempts = QuizAttempt::where('quiz_id', $course->pretest->id)
                ->where('user_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($course->posttest) {
            $posttestAttempts = QuizAttempt::where('quiz_id', $course->posttest->id)
                ->where('user_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Get lesson progress
        $lessonProgress = LessonProgress::where('enrollment_id', $enrollment->id)
            ->with('lesson.module')
            ->orderBy('last_activity_at', 'desc')
            ->get();

        // Get activity logs for this student in this course
        $activityLogs = UserActivityLog::where('user_id', $student->id)
            ->where(function ($q) use ($course) {
                $q->where(function ($qq) use ($course) {
                    $qq->where('subject_type', Course::class)
                       ->where('subject_id', $course->id);
                })
                ->orWhereIn('subject_id', $course->lessons()->pluck('id'))
                ->orWhereIn('subject_id', function ($query) use ($course) {
                    $query->select('id')
                        ->from('quizzes')
                        ->where('quizzable_type', Course::class)
                        ->where('quizzable_id', $course->id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('instructor.pages.courses.student-progress', compact(
            'course',
            'student',
            'enrollment',
            'pretestAttempts',
            'posttestAttempts',
            'lessonProgress',
            'activityLogs'
        ));
    }
}
