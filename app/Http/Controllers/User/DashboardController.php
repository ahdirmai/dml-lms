<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\LessonProgress;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. User info
        $user = Auth::user();
        $userInfo = [
            'name'     => $user->name,
            'position' => $user->position ?? 'Karyawan',
            'vessel'   => $user->vessel ?? 'N/A',
            'rankId'   => $user->rankId ?? null,
        ];

        // 2. Ambil data enrollments nyata
        $enrollments = $this->getRealCoursesData($user->id);

        // 3. Format jadi array untuk view (TERMASUK URL PRE/POST/REVIEW)
        $coursesArray = $this->formatEnrollmentsForView($enrollments);

        // 4. Dummy leaderboard
        $leaderboardData = $this->getDummyLeaderboard();

        // 5. Statistik performa
        $performance = $this->calculatePerformance($coursesArray);

        return view('user.dashboard.index', [
            'userInfo'       => $userInfo,
            'courses'        => $coursesArray,
            'leaderboardData' => $leaderboardData,
            'performance'    => $performance,
        ]);
    }

    private function getRealCoursesData(string $userId): Collection
    {
        return Enrollment::where('user_id', $userId)
            ->with([
                'course' => function ($query) {
                    $query
                        ->withCount(['modules', 'lessons'])
                        ->withSum('lessons', 'duration_minutes')
                        ->with([
                            'categories',
                            'instructor',
                            'modules' => function ($q_mod) {
                                $q_mod->withSum('lessons', 'duration_minutes')
                                    ->with('lessons:id,module_id')
                                    ->orderBy('order');
                            },
                            'pretest.questions.options',
                            'posttest.questions.options',
                        ]);
                },
                'lessonProgress',
                'latestPretestAttempt',
                'latestPosttestAttempt',
                'dueDate',
            ])
            ->get();
    }

    private function formatEnrollmentsForView(Collection $enrollments): array
    {
        return $enrollments->map(function (Enrollment $enrollment) {
            $course = $enrollment->course;

            if (!$course) {
                return null;
            }

            $lessonProgress   = $enrollment->lessonProgress;
            $totalLessons     = $course->lessons_count ?? 0;
            $completedLessons = $lessonProgress->where('status', 'completed')->count();
            $hasInProgress    = $lessonProgress->where('status', 'in_progress')->isNotEmpty();

            $pretestScore  = $enrollment->latestPretestAttempt?->score;
            $posttestScore = $enrollment->latestPosttestAttempt?->score;

            // ===== Progress & Status =====
            $moduleProgress = 0;
            if ($totalLessons > 0) {
                $moduleProgress = ($completedLessons / $totalLessons) * 90;
            } elseif ($pretestScore !== null) {
                $moduleProgress = 10;
            }

            $postTestProgress = $posttestScore !== null ? 10 : 0;
            $progress = round($moduleProgress + $postTestProgress);

            $dueDate = $enrollment->dueDate?->end_date;
            $status  = 'Not Started';

            if ($dueDate && now()->gt($dueDate) && $enrollment->status !== 'completed') {
                $status = 'Expired';
            } elseif ($progress >= 100 || $enrollment->status === 'completed') {
                $status   = 'Completed';
                $progress = 100;
            } elseif ($completedLessons > 0 || $hasInProgress || $pretestScore !== null) {
                $status = 'In Progress';
            }

            // ===== Modul =====
            $modulesData = [];
            $courseInProgress          = ($status === 'In Progress');
            $firstUnlockedModuleFound  = false;

            foreach ($course->modules as $module) {
                $lessonIds          = $module->lessons->pluck('id');
                $totalModuleLessons = $lessonIds->count();
                $completedCount     = $lessonProgress
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('status', 'completed')
                    ->count();

                $inProgress = $lessonProgress
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('status', 'in_progress')
                    ->isNotEmpty();

                $modStatus = 'locked';
                if ($totalModuleLessons > 0 && $completedCount === $totalModuleLessons) {
                    $modStatus = 'completed';
                    $firstUnlockedModuleFound = true;
                } elseif ($inProgress || $completedCount > 0) {
                    $modStatus = 'in-progress';
                    $firstUnlockedModuleFound = true;
                } elseif ($courseInProgress && !$firstUnlockedModuleFound && $pretestScore !== null) {
                    $modStatus = 'in-progress';
                    $firstUnlockedModuleFound = true;
                }

                $modulesData[] = [
                    'no'       => $module->order,
                    'title'    => $module->title,
                    'duration' => $module->lessons_sum_duration_minutes ?? 0,
                    'status'   => $modStatus,
                ];
            }

            // ===== Soal PreTest =====
            $preTest = $course->pretest?->questions->map(function ($q) {
                return [
                    'q'       => $q->question_text,
                    'options' => $q->options->sortBy('order_no')->pluck('option_text')->values()->all(),
                    'answer'  => $q->options->sortBy('order_no')->values()->search(fn($opt) => $opt->is_correct),
                ];
            })->values()->all() ?? [];

            // ===== Soal PostTest =====
            $postTest = $course->posttest?->questions->map(function ($q) {
                return [
                    'q'       => $q->question_text,
                    'options' => $q->options->sortBy('order_no')->pluck('option_text')->values()->all(),
                    'answer'  => $q->options->sortBy('order_no')->values()->search(fn($opt) => $opt->is_correct),
                ];
            })->values()->all() ?? [];

            // ===== URL Submit (DIKIRIM KE KOMPONEN) =====
            //  -> silakan sesuaikan nama route dengan punya kamu
            $submitPreUrl  = $course->pretest
                ? route('user.courses.test.submit', ['course' => $course, 'type' => 'pre'])
                : null;

            $submitPostUrl = $course->posttest
                ? route('user.courses.test.submit', ['course' => $course, 'type' => 'post'])
                : null;

            $submitReviewUrl = route('user.courses.review.submit', $course);

            // ===== Return Array Final =====
            return [
                'id'              => $course->id,
                'title'           => $course->title,
                'subtitle'        => $course->subtitle,
                'category'        => $course->categories->first()?->name ?? 'Uncategorized',
                'assignedOn'      => $enrollment->enrolled_at->format('d M Y'),
                'assignedBy'      => $course->instructor?->name ?? 'Administrator',
                'totalModules'    => $course->modules_count,
                'totalDuration'   => $course->lessons_sum_duration_minutes ?? 0,
                'preTestScore'    => $pretestScore,
                'postTestScore'   => $posttestScore,
                'progress'        => $progress,
                'lastActivity'    => $lessonProgress->max('last_activity_at')?->format('d M Y') ?? '-',
                'status'          => $status,
                'modules'         => $modulesData,
                'description'     => $course->description,
                'learningObjectives' => $course->learning_objectives ?? [],

                'preTest'         => $preTest,
                'postTest'        => $postTest,

                // >>> URL yang dipakai KOMPPONENT TEST MODAL (NO AJAX) <<<
                'submit_pre_url'    => $submitPreUrl,
                'submit_post_url'   => $submitPostUrl,
                'submit_review_url' => $submitReviewUrl,
            ];
        })->filter()->all();
    }

    private function calculatePerformance(array $courses): array
    {
        $stats = [
            'total'           => 0,
            'completed'       => 0,
            'inProgress'      => 0,
            'notStarted'      => 0,
            'expired'         => 0,
            'totalModules'    => 0,
            'completedModules' => 0,
        ];

        foreach ($courses as $course) {
            $stats['total']++;
            $stats['totalModules'] += $course['totalModules'];

            switch ($course['status']) {
                case 'Completed':
                    $stats['completed']++;
                    break;
                case 'In Progress':
                    $stats['inProgress']++;
                    break;
                case 'Not Started':
                    $stats['notStarted']++;
                    break;
                case 'Expired':
                    $stats['expired']++;
                    break;
            }

            foreach ($course['modules'] as $module) {
                if ($module['status'] === 'completed') {
                    $stats['completedModules']++;
                }
            }
        }

        $stats['overallProgress'] = $stats['total'] > 0
            ? round(($stats['completed'] / $stats['total']) * 100)
            : 0;

        $complianceGoal = 3;
        $stats['complianceGoal']    = $complianceGoal;
        $stats['complianceProgress'] = min(100, round(($stats['completed'] / $complianceGoal) * 100));

        return $stats;
    }

    private function getDummyLeaderboard(): array
    {
        return [
            'postTest' => [
                ['name' => "Siti", 'score' => 98, 'category' => "HSSE", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Agus", 'score' => 95, 'category' => "Operation", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Dewi", 'score' => 92, 'category' => "Finance", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Budi Santoso", 'score' => 88, 'isYou' => true,  'rank' => 4, 'icon' => 'user'],
                ['name' => "Joko Widodo", 'score' => 85, 'isYou' => false, 'rank' => 5, 'icon' => 'user'],
                ['name' => "Rina Wijaya", 'score' => 80, 'isYou' => false, 'rank' => 6, 'icon' => 'user'],
                ['name' => "Herman Kusuma", 'score' => 75, 'isYou' => false, 'rank' => 7, 'icon' => 'user'],
            ],
            'completedCourses' => [
                ['name' => "Siti",          'count' => 8, 'category' => "HSSE",     'isYou' => false, 'icon' => 'user'],
                ['name' => "Joko",          'count' => 7, 'category' => "IT",       'isYou' => false, 'icon' => 'user'],
                ['name' => "Agus",          'count' => 6, 'category' => "Operation", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Dewi Lestari",  'count' => 5, 'isYou' => false, 'rank' => 4, 'icon' => 'user'],
                ['name' => "Herman Kusuma", 'count' => 5, 'isYou' => false, 'rank' => 5, 'icon' => 'user'],
                ['name' => "Budi Santoso",  'count' => 4, 'isYou' => true,  'rank' => 6, 'icon' => 'user'],
                ['name' => "Rina Wijaya",   'count' => 3, 'isYou' => false, 'rank' => 7, 'icon' => 'user'],
            ],
        ];
    }
}
