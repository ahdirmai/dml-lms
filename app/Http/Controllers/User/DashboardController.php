<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course; // Tambahkan
use App\Models\Lms\Enrollment; // Tambahkan
use App\Models\Lms\LessonProgress; // Tambahkan
use App\Models\Lms\Quiz; // Tambahkan
use App\Models\Lms\QuizAttempt; // Tambahkan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan
use Illuminate\Database\Eloquent\Collection; // Tambahkan

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Dapatkan User Info dari Auth
        $user = Auth::user();
        $userInfo = [
            'name' => $user->name,
            // Asumsikan 'position' ada di model User Anda.
            // Jika tidak, ganti dengan 'Karyawan' atau data lain.
            'position' => $user->position ?? 'Karyawan',
            'vessel' => $user->vessel ?? 'N/A',
            'rankId' => $user->rankId ?? null,
        ];

        // 2. Dapatkan Data Kursus Nyata
        $enrollments = $this->getRealCoursesData($user->id);

        // 3. Format data agar sesuai dengan blade
        $coursesArray = $this->formatEnrollmentsForView($enrollments);

        // 4. Dummy Leaderboard Data (Sesuai permintaan)
        $leaderboardData = $this->getDummyLeaderboard();

        // 5. Calculate Performance Stats (menggunakan data $coursesArray yang baru)
        $performance = $this->calculatePerformance($coursesArray);

        return view('user.dashboard.index', [
            'userInfo' => $userInfo,
            'courses' => $coursesArray, // Gunakan data yang sudah diformat
            'leaderboardData' => $leaderboardData,
            'performance' => $performance,
        ]);
    }

    /**
     * Helper untuk mengambil data kursus nyata dari database
     * menggunakan Eager Loading untuk menghindari N+1 query.
     */
    private function getRealCoursesData(string $userId): Collection
    {
        return Enrollment::where('user_id', $userId)
            ->with([
                // Load relasi course
                'course' => function ($query) {
                    $query
                        // Hitung total modul & total durasi pelajaran
                        ->withCount(['modules', 'lessons'])
                        ->withSum('lessons', 'duration_minutes')
                        // Load relasi dari course
                        ->with([
                            'categories', // Untuk nama kategori
                            'instructor', // Untuk 'assignedBy'
                            // Load modul, pelajaran di dalamnya, dan total durasinya
                            'modules' => function ($q_mod) {
                                $q_mod->withSum('lessons', 'duration_minutes')
                                    ->with('lessons:id,module_id') // Hanya perlu ID pelajaran
                                    ->orderBy('order');
                            },
                            // Load pre/post test, pertanyaan, dan opsi
                            'pretest.questions.options',
                            'posttest.questions.options',
                        ]);
                },
                // Load semua progress pelajaran
                'lessonProgress',
                // Load attempt kuis terakhir (relasi baru di Enrollment.php)
                'latestPretestAttempt',
                'latestPosttestAttempt',
                // Load tanggal jatuh tempo
                'dueDate'
            ])
            ->get();
    }

    /**
     * Helper untuk mengubah Collection Eloquent menjadi array
     * yang sesuai dengan struktur blade/JS.
     */
    private function formatEnrollmentsForView(Collection $enrollments): array
    {
        return $enrollments->map(function (Enrollment $enrollment) {
            $course = $enrollment->course;

            // Jika course tidak (lagi) ada, lewati
            if (!$course) {
                return null;
            }

            $lessonProgress = $enrollment->lessonProgress;
            $totalLessons = $course->lessons_count ?? 0;
            $completedLessons = $lessonProgress->where('status', 'completed')->count();
            $hasInProgress = $lessonProgress->where('status', 'in_progress')->isNotEmpty();

            $pretestScore = $enrollment->latestPretestAttempt?->score;
            $posttestScore = $enrollment->latestPosttestAttempt?->score;

            // --- Logika Progress & Status ---
            $moduleProgress = 0;
            if ($totalLessons > 0) {
                // 90% progress berasal dari penyelesaian modul/pelajaran
                $moduleProgress = ($completedLessons / $totalLessons) * 90;
            } elseif ($pretestScore !== null) {
                // Jika tidak ada pelajaran, pretest terisi = 10%
                $moduleProgress = 10;
            }

            // 10% progress dari post-test
            $postTestProgress = $posttestScore !== null ? 10 : 0;
            $progress = round($moduleProgress + $postTestProgress);

            // Tentukan Status
            $dueDate = $enrollment->dueDate?->end_date;
            $status = 'Not Started';

            if ($dueDate && now()->gt($dueDate) && $enrollment->status !== 'completed') {
                $status = 'Expired';
            } elseif ($progress >= 100 || $enrollment->status === 'completed') {
                $status = 'Completed';
                $progress = 100;
            } elseif ($completedLessons > 0 || $hasInProgress || $pretestScore !== null) {
                $status = 'In Progress';
            }

            // --- Format Data Modul ---
            $modulesData = [];
            $courseInProgress = ($status === 'In Progress');
            $firstUnlockedModuleFound = false; // Untuk menentukan modul 'in-progress' pertama

            foreach ($course->modules as $module) {
                $lessonIds = $module->lessons->pluck('id');
                $totalModuleLessons = $lessonIds->count();
                $completedCount = $lessonProgress->whereIn('lesson_id', $lessonIds)->where('status', 'completed')->count();
                $inProgress = $lessonProgress->whereIn('lesson_id', $lessonIds)->where('status', 'in_progress')->isNotEmpty();

                $modStatus = 'locked';
                if ($totalModuleLessons > 0 && $completedCount === $totalModuleLessons) {
                    $modStatus = 'completed';
                    $firstUnlockedModuleFound = true;
                } elseif ($inProgress || $completedCount > 0) {
                    $modStatus = 'in-progress';
                    $firstUnlockedModuleFound = true;
                } elseif ($courseInProgress && !$firstUnlockedModuleFound && $pretestScore !== null) {
                    // Jika kursus sedang berjalan, pretest selesai, dan ini modul pertama
                    // yang belum selesai, tandai sebagai 'in-progress'.
                    $modStatus = 'in-progress';
                    $firstUnlockedModuleFound = true;
                }

                $modulesData[] = [
                    'no' => $module->order,
                    'title' => $module->title,
                    'duration' => $module->lessons_sum_duration_minutes ?? 0,
                    'status' => $modStatus,
                ];
            }

            // --- Format Data Kuis (untuk modal JS) ---
            $preTest = $course->pretest?->questions->map(function ($q) {
                return [
                    'q' => $q->question_text,
                    'options' => $q->options->sortBy('order_no')->pluck('option_text')->all(),
                    // Cari index dari jawaban yang benar
                    'answer' => $q->options->search(fn($opt) => $opt->is_correct),
                ];
            }) ?? [];

            $postTest = $course->posttest?->questions->map(function ($q) {
                return [
                    'q' => $q->question_text,
                    'options' => $q->options->sortBy('order_no')->pluck('option_text')->all(),
                    'answer' => $q->options->search(fn($opt) => $opt->is_correct),
                ];
            }) ?? [];

            // --- Return Array Final ---
            return [
                'id' => $course->id, // JS menggunakan ini sebagai courseId
                'title' => $course->title,
                'subtitle' => $course->subtitle,
                'category' => $course->categories->first()?->name ?? 'Uncategorized',
                'assignedOn' => $enrollment->enrolled_at->format('d M Y'),
                'assignedBy' => $course->instructor?->name ?? 'Administrator',
                'totalModules' => $course->modules_count,
                'totalDuration' => $course->lessons_sum_duration_minutes ?? 0,
                'preTestScore' => $pretestScore,
                'postTestScore' => $posttestScore,
                'progress' => $progress,
                'lastActivity' => $lessonProgress->max('last_activity_at')?->format('d M Y') ?? '-',
                'status' => $status,
                'modules' => $modulesData,
                'description' => $course->description,
                'learningObjectives' => $course->learning_objectives ?? [],
                'preTest' => $preTest,
                'postTest' => $postTest,
            ];
        })->filter()->all(); // filter() menghapus nilai null
    }

    /**
     * Helper untuk menghitung statistik performa.
     * Logika ini tidak perlu diubah karena kita memformat data nyata
     * agar sesuai dengan struktur yang diharapkan.
     */
    private function calculatePerformance(array $courses): array
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

        // Calculate Overall Progress
        $stats['overallProgress'] = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;

        // Mock Compliance Goal
        $complianceGoal = 3;
        $stats['complianceGoal'] = $complianceGoal;
        $stats['complianceProgress'] = min(100, round(($stats['completed'] / $complianceGoal) * 100));


        return $stats;
    }

    /**
     * Helper untuk data leaderboard. (Tetap dummy)
     */
    private function getDummyLeaderboard(): array
    {
        return [
            'postTest' => [
                ['name' => "Siti", 'score' => 98, 'category' => "HSSE", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Agus", 'score' => 95, 'category' => "Operation", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Dewi", 'score' => 92, 'category' => "Finance", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Budi Santoso", 'score' => 88, 'isYou' => true, 'rank' => 4, 'icon' => 'user'],
                ['name' => "Joko Widodo", 'score' => 85, 'isYou' => false, 'rank' => 5, 'icon' => 'user'],
                ['name' => "Rina Wijaya", 'score' => 80, 'isYou' => false, 'rank' => 6, 'icon' => 'user'],
                ['name' => "Herman Kusuma", 'score' => 75, 'isYou' => false, 'rank' => 7, 'icon' => 'user'],
            ],
            'completedCourses' => [
                ['name' => "Siti", 'count' => 8, 'category' => "HSSE", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Joko", 'count' => 7, 'category' => "IT", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Agus", 'count' => 6, 'category' => "Operation", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Dewi Lestari", 'count' => 5, 'isYou' => false, 'rank' => 4, 'icon' => 'user'],
                ['name' => "Herman Kusuma", 'count' => 5, 'isYou' => false, 'rank' => 5, 'icon' => 'user'],
                ['name' => "Budi Santoso", 'count' => 4, 'isYou' => true, 'rank' => 6, 'icon' => 'user'],
                ['name' => "Rina Wijaya", 'count' => 3, 'isYou' => false, 'rank' => 7, 'icon' => 'user'],
            ]
        ];
    }

    /**
     * Helper untuk data kursus.
     * (TIDAK LAGI DIGUNAKAN, diganti oleh getRealCoursesData)
     */
    // private function getDummyCourses(): array { ... }
}
