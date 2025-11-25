<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\LessonProgress;
use App\Models\Lms\QuizAttempt;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class CourseProgressController extends Controller
{
    /**
     * Tampilkan halaman Progress Tracking sebuah course.
     * Saat ini pakai DUMMY DATA; query real data ada di komentar.
     */
    /**
     * Tampilkan halaman Progress Tracking sebuah course.
     */
    public function show(Request $request, Course $course)
    {
        // 1. Base Query for Enrollments
        $query = $course->enrollments()
            ->with(['user', 'lessonProgress']);

        // 2. Filtering
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 3. Get Paginated Results
        $enrollments = $query->latest('enrolled_at')->paginate(10);

        // 4. Calculate Progress for each student
        // We need total lessons count for the course to calculate percentage
        $totalLessons = $course->lessons()->count();

        $students = $enrollments->map(function ($enrollment) use ($totalLessons) {
            // Count completed lessons from lessonProgress relation
            $completedCount = $enrollment->lessonProgress
                ->where('status', 'completed')
                ->count();
            
            $progressPercent = $totalLessons > 0 
                ? round(($completedCount / $totalLessons) * 100) 
                : 0;

            // Last activity
            $lastActivity = $enrollment->lessonProgress
                ->sortByDesc('last_activity_at')
                ->first()
                ?->last_activity_at;

            return [
                'id'                => $enrollment->user->id,
                'name'              => $enrollment->user->name,
                'email'             => $enrollment->user->email,
                'status'            => $enrollment->status,
                'progress'          => $progressPercent,
                'completed_lessons' => $completedCount,
                'total_lessons'     => $totalLessons,
                'last_activity'     => $lastActivity ? $lastActivity->format('Y-m-d H:i') : null,
                'enrolled_at'       => $enrollment->enrolled_at,
            ];
        });

        // Filter by progress range (if requested) - Note: This is done on the current page only if we use pagination
        // Ideally, we should filter in query, but calculating progress in SQL can be complex. 
        // For now, let's keep it simple. If user wants to filter by progress, we might need a more complex query or join.
        // Given the current requirement, let's apply it to the collection if strictly needed, 
        // but for pagination consistency, it's better to do it in SQL or ignore for now if not critical.
        // Let's implement a basic collection filter for the current page if requested, 
        // but acknowledge it won't filter the whole DB. 
        // BETTER APPROACH: Calculate progress in SQL to allow filtering.
        // However, for this iteration, let's stick to the plan and maybe handle progress filtering later if needed or do it in memory for small datasets.
        // Let's skip complex SQL for progress filtering for now to keep it simple as per plan.
        
        if ($request->filled('progress_range')) {
            [$min, $max] = explode('-', $request->input('progress_range'));
            $students = $students->filter(function ($s) use ($min, $max) {
                return $s['progress'] >= $min && $s['progress'] <= $max;
            });
        }

        // 5. Summary Statistics (Global for the course, not filtered)
        $allEnrollments = $course->enrollments()->select('id', 'status')->get();
        // To calculate average progress, we need to load lesson progress for all. 
        // This might be heavy. Let's do a lighter query or approximation.
        // Or just use the current page's average? No, that's misleading.
        // Let's try to get a rough average or just count statuses.
        
        // Optimization: Calculate average progress via SQL
        // Assuming 'lesson_progress' table has 'enrollment_id' and 'status'='completed'
        // We can count completed lessons per enrollment.
        
        $totalCompletedLessonsAllUsers = \App\Models\Lms\LessonProgress::whereIn('enrollment_id', $allEnrollments->pluck('id'))
            ->where('status', 'completed')
            ->count();
            
        $totalPossibleLessons = $allEnrollments->count() * $totalLessons;
        $avgProgress = ($totalPossibleLessons > 0) 
            ? round(($totalCompletedLessonsAllUsers / $totalPossibleLessons) * 100) 
            : 0;

        $summary = [
            'students_total'     => $allEnrollments->count(),
            'students_active'    => $allEnrollments->where('status', 'active')->count(),
            'students_completed' => $allEnrollments->where('status', 'completed')->count(),
            'avg_progress'       => $avgProgress,
        ];

        // 6. Module Breakdown
        $moduleBreakdown = $course->modules()
            ->withCount('lessons')
            ->with(['lessons' => function($q) {
                $q->select('id', 'module_id');
            }])
            ->get()
            ->map(function ($module) use ($allEnrollments) {
                $lessonIds = $module->lessons->pluck('id');
                
                // Count total completed instances of these lessons across all enrollments
                $completedCount = \App\Models\Lms\LessonProgress::whereIn('lesson_id', $lessonIds)
                    ->whereIn('enrollment_id', $allEnrollments->pluck('id'))
                    ->where('status', 'completed')
                    ->count();

                // Total possible completions = lessons in module * total students
                $totalPossible = $module->lessons_count * $allEnrollments->count();

                return [
                    'id'                => $module->id,
                    'title'             => $module->title,
                    'lessons_total'     => $module->lessons_count,
                    // We can show average completion count per student, or total completed events
                    // The UI shows "X / Y lessons", implying per student average? 
                    // Or maybe it's better to show percentage of completion for this module globally.
                    // Let's stick to the UI: "X / Y lessons" might be confusing for aggregate.
                    // Let's pass the raw numbers to calculate percentage.
                    'lessons_completed' => $allEnrollments->count() > 0 ? round($completedCount / $allEnrollments->count(), 1) : 0,
                    'lessons_count'     => $module->lessons_count
                ];
            });

        return view('admin.pages.courses.progress', compact(
            'course',
            'summary',
            'moduleBreakdown',
            'students',
            'enrollments' // Pass paginator
        ));
    }

    /**
     * Show detailed progress for a specific student (Admin has access to all courses)
     */
    public function showStudent(Course $course, User $student)
    {
        // Admin can access any course, no ownership check needed

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

        // Use admin-specific view
        return view('admin.pages.courses.student-progress', compact(
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
