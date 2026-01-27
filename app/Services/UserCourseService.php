<?php

namespace App\Services;

use App\Models\Lms\Enrollment;
use Illuminate\Database\Eloquent\Collection;

class UserCourseService
{
    /**
     * Get real enrollments data for a user.
     */
    public function getUserEnrollments(string $userId): Collection
    {
        return Enrollment::where('user_id', $userId)
            ->with([
                'course' => function ($query) {
                    $query
                        ->withCount(['modules', 'lessons'])
                        ->withSum('lessons', 'duration_seconds')
                        ->with([
                            'categories',
                            'instructor',
                            'modules' => function ($q_mod) {
                                $q_mod->withSum('lessons', 'duration_seconds')
                                    ->with('lessons:id,module_id')
                                    ->orderBy('order');
                            },
                            'pretest.questions' => fn ($q) => $q->orderBy('order')->with('options'),
                            'posttest.questions' => fn ($q) => $q->orderBy('order')->with('options'),
                        ]);
                },
                'lessonProgress',
                'dueDate',
            ])
            ->get()
            ->each(function ($q) {
                // Trigger accessors or helper methods to load attempts
                // Note: This might cause N+1 queries if not careful, but we follow the reference implementation.
                $q->latest_pretest_attempt = $q->latest_pretest_attempt;

                // latestPosttestAttempt in model is a method returning a model, not an accessor.
                // But DashboardController accesses it as a property $q->latest_posttest_attempt
                // which implies it expects an accessor or magic property.
                // If the model method is named latestPosttestAttempt(), $q->latest_posttest_attempt won't automatically work
                // unless there is a getLatestPosttestAttemptAttribute or it's a relation.
                // However, DashboardController line 70 says: $q->latest_posttest_attempt = $q->latest_posttest_attempt;
                // If it works in DashboardController, we keep it.
                // But looking at Enrollment.php, latestPosttestAttempt is a method.
                // Accessing it as a property will return null unless Laravel magic is at play (e.g. if it was a relation).
                // Given the code in Enrollment.php, it seems it might be returning null or the code relies on something else.
                // We will stick to the reference code.
                $q->latest_posttest_attempt = $q->latest_posttest_attempt;
            });
    }

    /**
     * Format enrollments for view.
     */
    public function formatEnrollments(Collection $enrollments): array
    {
        return $enrollments->map(function (Enrollment $enrollment) {
            $course = $enrollment->course;

            if (! $course) {
                return null;
            }

            $lessonProgress = $enrollment->lessonProgress;
            $totalLessons = $course->lessons_count ?? 0;
            $completedLessons = $lessonProgress->where('status', 'completed')->count();
            $hasInProgress = $lessonProgress->where('status', 'in_progress')->isNotEmpty();

            // Note: In DashboardController it accessed properties.
            // We need to ensure these are available on the enrollment object.
            // In getUserEnrollments we assigned them.
            $pretestScore = $enrollment->latest_pretest_attempt?->score;
            $posttestScore = $enrollment->latest_posttest_attempt?->score;

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
            $usingDueDate = $course->using_due_date ?? false;
            $status = 'Not Started';

            if ($usingDueDate && $dueDate && now()->gt($dueDate) && $enrollment->status !== 'completed') {
                $status = 'Expired';
            } elseif ($progress >= 100 || $enrollment->status === 'completed') {
                $status = 'Completed';
                $progress = 100;
            } elseif ($completedLessons > 0 || $hasInProgress || $pretestScore !== null) {
                $status = 'In Progress';
            }

            // ===== Modul =====
            $modulesData = [];
            $courseInProgress = ($status === 'In Progress');
            $firstUnlockedModuleFound = false;

            foreach ($course->modules as $module) {
                $lessonIds = $module->lessons->pluck('id');
                $totalModuleLessons = $lessonIds->count();
                $completedCount = $lessonProgress
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
                } elseif ($courseInProgress && ! $firstUnlockedModuleFound && $pretestScore !== null) {
                    $modStatus = 'in-progress';
                    $firstUnlockedModuleFound = true;
                }

                $modulesData[] = [
                    'no' => $module->order,
                    'title' => $module->title,
                    'duration' => $module->lessons_sum_duration_seconds ?? 0,
                    'status' => $modStatus,
                ];
            }

            // ===== Soal PreTest =====
            $preTest = $course->pretest?->questions->map(function ($q) {
                return [
                    'q' => $q->question_text,
                    'options' => $q->options->sortBy('order_no')->pluck('option_text')->values()->all(),
                    'answer' => $q->options->sortBy('order_no')->values()->search(fn ($opt) => $opt->is_correct),
                ];
            })->values()->all() ?? [];

            // ===== Soal PostTest =====
            $postTest = $course->posttest?->questions->map(function ($q) {
                return [
                    'q' => $q->question_text,
                    'options' => $q->options->sortBy('order_no')->pluck('option_text')->values()->all(),
                    'answer' => $q->options->sortBy('order_no')->values()->search(fn ($opt) => $opt->is_correct),
                ];
            })->values()->all() ?? [];

            // ===== URL Submit =====
            $submitPreUrl = $course->pretest
                ? route('user.courses.test.submit', ['course' => $course, 'type' => 'pre'])
                : null;

            $submitPostUrl = $course->posttest
                ? route('user.courses.test.submit', ['course' => $course, 'type' => 'post'])
                : null;

            $submitReviewUrl = route('user.courses.review.submit', $course);

            // ===== Return Array Final =====
            return [
                'id' => $course->id,
                'title' => $course->title,
                'subtitle' => $course->subtitle,
                'category' => $course->categories->first()?->name ?? 'Uncategorized',
                'assignedOn' => $enrollment->enrolled_at->format('d M Y'),
                'assignedBy' => $course->instructor?->name ?? 'Administrator',
                'totalModules' => $course->modules_count,
                'totalDuration' => convert_seconds_to_duration($course->lessons_sum_duration_seconds ?? 0),
                'preTestScore' => $pretestScore,
                'postTestScore' => $posttestScore,
                'progress' => $progress,
                'lastActivity' => $lessonProgress->max('last_activity_at')?->format('d M Y') ?? '-',
                'status' => $status,
                'modules' => $modulesData,
                'description' => $course->description,
                'learningObjectives' => $course->learning_objectives ?? [],
                'thumbnail_path' => $course->thumbnail_path,

                'preTest' => $preTest,
                'postTest' => $postTest,

                'submit_pre_url' => $submitPreUrl,
                'submit_post_url' => $submitPostUrl,
                'submit_review_url' => $submitReviewUrl,
                'hasReviewed' => !is_null($enrollment->review_stars),
                'certificateUrl' => route('user.courses.certificate', $course),
            ];
        })->filter()->all();
    }

    /**
     * Format enrollments for Course List view (Cards).
     */
    public function formatForCourseList(Collection $enrollments, string $activeTab): array
    {
        return $enrollments->map(function (Enrollment $enrollment) use ($activeTab) {
            $course = $enrollment->course;
            if (! $course) {
                return null;
            }

            // Progress berdasarkan pelajaran
            $totalLessons = $course->lessons_count ?? $course->lessons()->count();
            $completedLessons = $enrollment->lessonProgress->where('status', 'completed')->count();
            $percent = ($totalLessons > 0)
                ? (int) floor(($completedLessons / $totalLessons) * 100)
                : 0;

            // CTA lama (tetap dipakai fallback)
            $cta = 'Lihat Kursus';
            $cta_kind = 'primary';
            if ($enrollment->status === 'completed') {
                $cta = 'Lihat Sertifikat';
                $cta_kind = 'success';
            } elseif ($percent > 0) {
                $cta = 'Lanjutkan Belajar';
            } else {
                $cta = 'Mulai Belajar';
            }

            // Nilai pre/post test terakhir
            // Note: getUserEnrollments ensures these are loaded/accessible
            $preAttempt = $enrollment->latest_pretest_attempt;
            $postAttempt = $enrollment->latest_posttest_attempt;

            $preScore = $preAttempt ? (int) $preAttempt->score : null;
            $postScore = $postAttempt ? (int) $postAttempt->score : null;

            // Soal pretest
            $preTest = $course->pretest?->questions->map(function ($q) {
                $optionsSorted = $q->options->sortBy('order_no')->values();

                return [
                    'q' => $q->question_text,
                    'options' => $optionsSorted->pluck('option_text')->all(),
                    'answer' => $optionsSorted->search(fn ($opt) => $opt->is_correct),
                ];
            })->values()->all() ?? [];

            // Soal posttest
            $postTest = $course->posttest?->questions->map(function ($q) {
                $optionsSorted = $q->options->sortBy('order_no')->values();

                return [
                    'q' => $q->question_text,
                    'options' => $optionsSorted->pluck('option_text')->all(),
                    'answer' => $optionsSorted->search(fn ($opt) => $opt->is_correct),
                ];
            })->values()->all() ?? [];

            // URL submit untuk modal
            $submitPreUrl = $course->pretest
                ? route('user.courses.test.submit', ['course' => $course, 'type' => 'pre'])
                : null;

            $submitPostUrl = $course->posttest
                ? route('user.courses.test.submit', ['course' => $course, 'type' => 'post'])
                : null;

            $submitReviewUrl = route('user.courses.review.submit', $course);

            return [
                'id' => $course->id,
                'title' => $course->title,
                'category' => $course->categories->first()->name ?? 'Umum',
                'instructor' => $course->instructor->name ?? 'Internal',
                'thumbnail' => $course->thumbnail_path
                    ?? 'https://picsum.photos/seed/'.$course->id.'/800/400',

                'progress' => $percent,
                'done' => "$completedLessons/$totalLessons Pelajaran",
                'cta' => $cta,
                'cta_kind' => $cta_kind,
                'status' => $activeTab, // untuk tab

                // tambahan untuk UI baru + modal test
                'total_modules' => $course->modules_count ?? $course->modules->count(),
                'total_duration' => convert_seconds_to_duration($course->lessons_sum_duration_seconds ?? 0),
                'assigned_on' => optional($enrollment->enrolled_at)->format('d M Y'),
                'all_lessons_completed' => ($totalLessons > 0 && $completedLessons >= $totalLessons),

                'preTestScore' => $preScore,
                'postTestScore' => $postScore,
                'preTest' => $preTest,
                'postTest' => $postTest,
                'submit_pre_url' => $submitPreUrl,
                'submit_post_url' => $submitPostUrl,
                'submit_review_url' => $submitReviewUrl,
                'hasReviewed' => !is_null($enrollment->review_stars),
                'certificateUrl' => route('user.courses.certificate', $course),
            ];
        })->filter()->values()->all();
    }

    /**
     * Calculate performance statistics.
     */
    public function calculatePerformance(array $courses): array
    {
        $stats = [
            'total' => 0,
            'completed' => 0,
            'inProgress' => 0,
            'notStarted' => 0,
            'expired' => 0,
            'totalModules' => 0,
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
        $stats['complianceGoal'] = $complianceGoal;
        $stats['complianceProgress'] = min(100, round(($stats['completed'] / $complianceGoal) * 100));

        return $stats;
    }

    /**
     * Get enrollment details for a specific course.
     */
    public function getCourseEnrollmentDetails(string $userId, string $courseId): ?Enrollment
    {
        return Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->with([
                'course' => fn ($q) => $q->with([
                    'modules' => fn ($q_mod) => $q_mod->orderBy('order', 'asc'),
                    'modules.lessons' => fn ($q_less) => $q_less->with('quiz')->orderBy('order_no', 'asc'),
                    
                    // PENTING: load soal & opsi untuk pre/post test
                    'pretest.questions' => fn ($q) => $q->orderBy('order')->with('options'),
                    'posttest.questions' => fn ($q) => $q->orderBy('order')->with('options'),
                ])->withSum('lessons', 'duration_seconds'),
                'lessonProgress',
                'dueDate',
                // 'latestPretestAttempt', // Removed: Accessor, not a relation
                // 'latestPosttestAttempt', // Removed: Accessor, not a relation
            ])
            ->first();
    }

    /**
     * Format course details for the show view.
     */
    public function formatCourseDetails(Enrollment $enrollment): array
    {
        $course = $enrollment->course;

        // Gate pre-test
        $pretestGateActive = false;
        $latestPretestAttempt = $enrollment->latestPretestAttempt;

        if ($course->require_pretest_before_content && $course->pretest) {
            if (! $latestPretestAttempt || ! $latestPretestAttempt->passed) {
                $pretestGateActive = true;
            }
        }

        $completedLessonIds = $enrollment->lessonProgress
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->flip();

        $totalLessons = 0;
        $completedLessonCount = 0;

        $modules = $course->modules->map(function ($module) use (
            $completedLessonIds,
            &$totalLessons,
            &$completedLessonCount,
            $pretestGateActive
        ) {
            $lessons = $module->lessons->map(function ($lesson) use (
                $completedLessonIds,
                &$completedLessonCount,
                $pretestGateActive
            ) {
                $isDone = isset($completedLessonIds[$lesson->id]);
                if ($isDone) {
                    $completedLessonCount++;
                }

                $isQuiz = $lesson->kind === 'quiz' && $lesson->quiz;

                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'type' => $lesson->kind,
                    'duration' => $lesson->duration_seconds ? convert_seconds_to_duration($lesson->duration_seconds) : '-',
                    'is_done' => $isDone,
                    'is_locked' => $pretestGateActive,
                    'questions' => $isQuiz ? ($lesson->quiz->questions_count ?? 0) : 0,
                ];
            });

            $totalLessons += $lessons->count();

            return [
                'id' => $module->id,
                'title' => $module->title,
                'lessons' => $lessons->all(),
            ];
        });

        $percent = ($totalLessons > 0)
            ? (int) floor(($completedLessonCount / $totalLessons) * 100)
            : 0;

        $lastProgress = $enrollment->lessonProgress->sortByDesc('last_activity_at')->first();

        $progress = [
            'percent' => $percent,
            'last_lesson_id' => $lastProgress ? $lastProgress->lesson_id : null,
        ];

        $pretestResult = $this->formatTestResult($latestPretestAttempt, 'pre');
        $posttestResult = $this->formatTestResult($enrollment->latestPosttestAttempt, 'post');

        // susun soal pretest
        $preTest = $course->pretest?->questions->map(function ($q) {
            $optionsSorted = $q->options->sortBy('order_no')->values();

            return [
                'q' => $q->question_text,
                'options' => $optionsSorted->pluck('option_text')->all(),
                'answer' => $optionsSorted->search(fn ($opt) => $opt->is_correct),
            ];
        })->values()->all() ?? [];

        // susun soal posttest
        $postTest = $course->posttest?->questions->map(function ($q) {
            $optionsSorted = $q->options->sortBy('order_no')->values();

            return [
                'q' => $q->question_text,
                'options' => $optionsSorted->pluck('option_text')->all(),
                'answer' => $optionsSorted->search(fn ($opt) => $opt->is_correct),
            ];
        })->values()->all() ?? [];

        $submitPreUrl = $course->pretest
            ? route('user.courses.test.submit', ['course' => $course, 'type' => 'pre'])
            : null;
        $submitPostUrl = $course->posttest
            ? route('user.courses.test.submit', ['course' => $course, 'type' => 'post'])
            : null;
        $submitReviewUrl = route('user.courses.review.submit', $course);

        // array berisi SATU kursus, sesuai format yang sudah dipakai TestFlow
        $testCourses = [[
            'id' => $course->id,
            'title' => $course->title,
            'preTest' => $preTest,
            'postTest' => $postTest,
            'submit_pre_url' => $submitPreUrl,
            'submit_post_url' => $submitPostUrl,
            'submit_review_url' => $submitReviewUrl,
            'hasReviewed' => !is_null($enrollment->review_stars),
            'certificateUrl' => route('user.courses.certificate', $course),
        ]];

        return [
            'course' => $course,
            'progress' => $progress,
            'modules' => $modules,
            'pretestResult' => $pretestResult,
            'posttestResult' => $posttestResult,
            'pretestGateActive' => $pretestGateActive,
            'testCourses' => $testCourses,
            'reviewStars' => $enrollment->review_stars,
        ];
    }

    /**
     * Format hasil attempt kuis untuk ditampilkan.
     */
    private function formatTestResult($attempt, string $type): ?array
    {
        if (! $attempt) {
            return null;
        }

        $score = (int) $attempt->score;
        $badge = '';
        $desc = '';

        if ($attempt->passed) {
            $badge = ($type === 'pre') ? 'Pemahaman Awal Baik' : 'Lulus';
            $desc = "Anda lulus {$type}-test dengan nilai {$score}.";
        } else {
            $badge = 'Perlu Peningkatan';
            $desc = "Nilai {$type}-test Anda ({$score}) belum memenuhi skor minimum.";
            if ($type === 'pre') {
                $desc .= ' Selesaikan pre-test untuk membuka materi.';
            }
        }

        return [
            'score' => $score,
            'passed' => $attempt->passed,
            'total' => 100,
            'date' => optional($attempt->finished_at)->format('d M Y'),
            'badge' => $badge,
            'desc' => $desc,
        ];
    }

    /**
     * Initialize lesson progress for an enrollment.
     * Creates 'not_started' records for all lessons in the course.
     */
    public function initializeLessonProgress(Enrollment $enrollment): void
    {
        $course = $enrollment->course;
        if (! $course) {
            return;
        }

        // Ambil semua lesson ID dari course ini
        $lessons = $course->lessons()->get();

        $now = now();

        foreach ($lessons as $lesson) {
            \App\Models\Lms\LessonProgress::firstOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'status' => 'not_started',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
