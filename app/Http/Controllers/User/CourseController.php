<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\LessonProgress;
use App\Models\Lms\QuizAnswer;
use App\Models\Lms\QuizAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Halaman "Kursus Saya"
     * - Data kursus + progress
     * - Tambahan: data pretest/posttest + URL submit (untuk modal)
     */
    public function index(Request $request)
    {
        $user      = Auth::user();
        $activeTab = $request->string('tab')->toString() ?: 'in_progress';

        $statusMap = [
            'in_progress' => ['assigned', 'active'],
            'completed'   => ['completed'],
            'private'     => [], // di-handle terpisah
        ];

        $baseEnrollments = $user->enrollments();

        $query = $baseEnrollments
            ->with([
                'course' => function ($q) {
                    $q->with([
                        'instructor',
                        'categories',
                        'pretest.questions.options',
                        'posttest.questions.options',
                    ])
                        ->withCount(['modules', 'lessons'])
                        ->withSum('lessons', 'duration_minutes');
                },
                'latestPretestAttempt',
                'latestPosttestAttempt',
            ])
            ->whereHas('course');

        if ($activeTab === 'private') {
            $query->whereHas('course', fn($q) => $q->where('status', 'private'));
        } else {
            $query->whereIn('status', $statusMap[$activeTab] ?? ['active']);
        }

        $enrollments = $query->get();

        $coursesData = $enrollments->map(function (Enrollment $enrollment) use ($activeTab) {
            $course = $enrollment->course;
            if (!$course) {
                return null;
            }

            // Progress berdasarkan pelajaran
            $totalLessons     = $course->lessons_count ?? $course->lessons()->count();
            $completedLessons = $enrollment->lessonProgress()->where('status', 'completed')->count();
            $percent          = ($totalLessons > 0)
                ? (int) floor(($completedLessons / $totalLessons) * 100)
                : 0;

            // CTA lama (tetap dipakai fallback)
            $cta      = 'Lihat Kursus';
            $cta_kind = 'primary';
            if ($enrollment->status === 'completed') {
                $cta      = 'Lihat Sertifikat';
                $cta_kind = 'success';
            } elseif ($percent > 0) {
                $cta = 'Lanjutkan Belajar';
            } else {
                $cta = 'Mulai Belajar';
            }

            // Nilai pre/post test terakhir
            $preAttempt  = $enrollment->latestPretestAttempt;
            $postAttempt = $enrollment->latestPosttestAttempt;

            $preScore  = $preAttempt ? (int) $preAttempt->score : null;
            $postScore = $postAttempt ? (int) $postAttempt->score : null;

            // Soal pretest
            $preTest = $course->pretest?->questions->map(function ($q) {
                $optionsSorted = $q->options->sortBy('order_no')->values();
                return [
                    'q'       => $q->question_text,
                    'options' => $optionsSorted->pluck('option_text')->all(),
                    'answer'  => $optionsSorted->search(fn($opt) => $opt->is_correct),
                ];
            })->values()->all() ?? [];

            // Soal posttest
            $postTest = $course->posttest?->questions->map(function ($q) {
                $optionsSorted = $q->options->sortBy('order_no')->values();
                return [
                    'q'       => $q->question_text,
                    'options' => $optionsSorted->pluck('option_text')->all(),
                    'answer'  => $optionsSorted->search(fn($opt) => $opt->is_correct),
                ];
            })->values()->all() ?? [];

            // URL submit untuk modal (sesuai route: user.courses.test.submit, user.courses.review.submit)
            $submitPreUrl  = $course->pretest
                ? route('user.courses.test.submit', ['course' => $course, 'type' => 'pre'])
                : null;

            $submitPostUrl = $course->posttest
                ? route('user.courses.test.submit', ['course' => $course, 'type' => 'post'])
                : null;

            $submitReviewUrl = route('user.courses.review.submit', $course);

            return [
                'id'         => $course->id,
                'title'      => $course->title,
                'category'   => $course->categories()->first()->name ?? 'Umum',
                'instructor' => $course->instructor->name ?? 'Internal',
                'thumbnail'  => $course->thumbnail_path
                    ?? 'https://picsum.photos/seed/' . $course->id . '/800/400',

                'progress'   => $percent,
                'done'       => "$completedLessons/$totalLessons Pelajaran",
                'cta'        => $cta,
                'cta_kind'   => $cta_kind,
                'status'     => $activeTab, // untuk tab

                // tambahan untuk UI baru + modal test
                'total_modules'   => $course->modules_count ?? $course->modules()->count(),
                'total_duration'  => $course->lessons_sum_duration_minutes ?? 0,
                'assigned_on'     => optional($enrollment->enrolled_at)->format('d M Y'),

                'preTestScore'    => $preScore,
                'postTestScore'   => $postScore,
                'preTest'         => $preTest,
                'postTest'        => $postTest,
                'submit_pre_url'  => $submitPreUrl,
                'submit_post_url' => $submitPostUrl,
                'submit_review_url' => $submitReviewUrl,
            ];
        })->filter()->values();

        $counts = [
            'in_progress' => (clone $baseEnrollments)->whereIn('status', ['assigned', 'active'])->count(),
            'completed'   => (clone $baseEnrollments)->where('status', 'completed')->count(),
            'private'     => (clone $baseEnrollments)->whereHas('course', fn($q) => $q->where('status', 'private'))->count(),
        ];

        return view('user.courses.index', [
            'activeTab' => $activeTab,
            'courses'   => $coursesData,
            'tabs'      => [
                'in_progress' => 'Sedang Dipelajari',
                'completed'   => 'Telah Selesai',
                'private'     => 'Kursus Private',
            ],
            'counts'    => $counts,
        ]);
    }

    /**
     * Detail kursus, modul, progress, dan hasil tes.
     */

    public function show(Request $request, string $courseId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->with([
                'course' => fn($q) => $q->with([
                    'modules' => fn($q_mod) => $q_mod->orderBy('order', 'asc'),
                    'modules.lessons' => fn($q_less) => $q_less->with('quiz')->orderBy('order_no', 'asc'),

                    // PENTING: load soal & opsi untuk pre/post test
                    'pretest.questions.options',
                    'posttest.questions.options',
                ]),
                'lessonProgress',
                'latestPretestAttempt',
                'latestPosttestAttempt',
            ])
            ->first();

        if (!$enrollment) {
            abort(403, 'Anda tidak terdaftar pada kursus ini.');
        }

        $course = $enrollment->course;

        // Gate pre-test
        $pretestGateActive    = false;
        $latestPretestAttempt = $enrollment->latestPretestAttempt;

        if ($course->require_pretest_before_content && $course->pretest) {
            if (!$latestPretestAttempt || !$latestPretestAttempt->passed) {
                $pretestGateActive = true;
            }
        }

        $completedLessonIds = $enrollment->lessonProgress
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->flip();

        $totalLessons         = 0;
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
                    'id'        => $lesson->id,
                    'title'     => $lesson->title,
                    'type'      => $lesson->kind,
                    'duration'  => $lesson->duration_minutes ? $lesson->duration_minutes . 'm' : '-',
                    'is_done'   => $isDone,
                    'is_locked' => $pretestGateActive,
                    'questions' => $isQuiz ? ($lesson->quiz->questions_count ?? 0) : 0,
                ];
            });

            $totalLessons += $lessons->count();

            return [
                'id'      => $module->id,
                'title'   => $module->title,
                'lessons' => $lessons->all(),
            ];
        });

        $percent = ($totalLessons > 0)
            ? (int) floor(($completedLessonCount / $totalLessons) * 100)
            : 0;

        $lastProgress = $enrollment->lessonProgress->sortByDesc('last_activity_at')->first();

        $progress = [
            'percent'        => $percent,
            'last_lesson_id' => $lastProgress ? $lastProgress->lesson_id : null,
        ];

        $pretestResult  = $this->formatTestResult($latestPretestAttempt, 'pre');
        $posttestResult = $this->formatTestResult($enrollment->latestPosttestAttempt, 'post');

        /**
         * ====== DATA UNTUK KOMPONEN <x-test.modals> ======
         * Format sama seperti yang dipakai di Dashboard / Kursus Saya:
         * - id, title
         * - preTest / postTest => daftar soal + opsi
         * - submit_pre_url / submit_post_url / submit_review_url
         */

        // susun soal pretest
        $preTest = $course->pretest?->questions->map(function ($q) {
            $optionsSorted = $q->options->sortBy('order_no')->values();

            return [
                'q'       => $q->question_text,
                'options' => $optionsSorted->pluck('option_text')->all(),
                'answer'  => $optionsSorted->search(fn($opt) => $opt->is_correct),
            ];
        })->values()->all() ?? [];

        // susun soal posttest
        $postTest = $course->posttest?->questions->map(function ($q) {
            $optionsSorted = $q->options->sortBy('order_no')->values();

            return [
                'q'       => $q->question_text,
                'options' => $optionsSorted->pluck('option_text')->all(),
                'answer'  => $optionsSorted->search(fn($opt) => $opt->is_correct),
            ];
        })->values()->all() ?? [];

        $submitPreUrl     = $course->pretest
            ? route('user.courses.test.submit', ['course' => $course, 'type' => 'pre'])
            : null;
        $submitPostUrl    = $course->posttest
            ? route('user.courses.test.submit', ['course' => $course, 'type' => 'post'])
            : null;
        $submitReviewUrl  = route('user.courses.review.submit', $course);

        // array berisi SATU kursus, sesuai format yang sudah dipakai TestFlow
        $testCourses = [[
            'id'               => $course->id,
            'title'            => $course->title,
            'preTest'          => $preTest,
            'postTest'         => $postTest,
            'submit_pre_url'   => $submitPreUrl,
            'submit_post_url'  => $submitPostUrl,
            'submit_review_url' => $submitReviewUrl,
        ]];

        return view('user.courses.show', compact(
            'course',
            'progress',
            'modules',
            'pretestResult',
            'posttestResult',
            'pretestGateActive',
            'testCourses',
        ));
    }


    /**
     * Format hasil attempt kuis untuk ditampilkan.
     */
    private function formatTestResult(?QuizAttempt $attempt, string $type): ?array
    {
        if (!$attempt) {
            return null;
        }

        $score = (int) $attempt->score;
        $badge = '';
        $desc  = '';

        if ($attempt->passed) {
            $badge = ($type === 'pre') ? 'Pemahaman Awal Baik' : 'Lulus';
            $desc  = "Anda lulus {$type}-test dengan nilai {$score}.";
        } else {
            $badge = 'Perlu Peningkatan';
            $desc  = "Nilai {$type}-test Anda ({$score}) belum memenuhi skor minimum.";
            if ($type === 'pre') {
                $desc .= ' Selesaikan pre-test untuk membuka materi.';
            }
        }

        return [
            'score' => $score,
            'total' => 100,
            'date'  => optional($attempt->finished_at)->format('d M Y'),
            'badge' => $badge,
            'desc'  => $desc,
        ];
    }

    /**
     * Menangani submit Pre-Test / Post-Test dari modal.
     * TANPA AJAX: form POST biasa, lalu redirect.
     *
     * Form dari modal mengirim:
     *  - answers[index] = optionIndex
     */
    public function submitTest(Request $request, Course $course, string $type)
    {
        $user = Auth::user();

        if (!in_array($type, ['pre', 'post'], true)) {
            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('error', 'Jenis tes tidak valid.');
        }

        try {
            $finalScore = 0;

            DB::transaction(function () use ($request, $course, $type, $user, &$finalScore) {
                $enrollment = Enrollment::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->firstOrFail();

                $quiz = ($type === 'pre') ? $course->pretest : $course->posttest;
                if (!$quiz) {
                    throw new \Exception('Kuis tidak ditemukan untuk kursus ini.');
                }

                $questions = $quiz->questions()->with('options')->get();
                $answers   = $request->input('answers', []); // array: index => optionIndex
                $totalQuestions = $questions->count();
                $correctCount   = 0;
                $now            = now();

                $attemptNo = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('user_id', $user->id)
                    ->max('attempt_no') ?? 0;
                $attemptNo++;

                $attempt = QuizAttempt::create([
                    'id'         => (string) Str::uuid(),
                    'quiz_id'    => $quiz->id,
                    'user_id'    => $user->id,
                    'attempt_no' => $attemptNo,
                    'started_at' => $now,
                    'finished_at' => $now,
                    'score'      => 0,
                    'passed'     => false,
                ]);

                $answersToCreate = [];

                foreach ($questions as $index => $question) {
                    $options       = $question->options->sortBy('order_no')->values();
                    $selectedIndex = $answers[$index] ?? null;

                    $selectedOption = null;
                    if ($selectedIndex !== null && isset($options[$selectedIndex])) {
                        $selectedOption = $options[$selectedIndex];
                    }

                    $isCorrect = $selectedOption && $selectedOption->is_correct;

                    if ($isCorrect) {
                        $correctCount++;
                    }

                    $answersToCreate[] = new QuizAnswer([
                        'id'                => (string) Str::uuid(),
                        'attempt_id'        => $attempt->id,
                        'question_id'       => $question->id,
                        'selected_option_id' => $selectedOption?->id,
                        'is_correct'        => $isCorrect,
                        'score_awarded'     => $isCorrect ? ($question->score ?? 1) : 0,
                    ]);
                }

                $attempt->answers()->saveMany($answersToCreate);

                $finalScore   = ($totalQuestions > 0)
                    ? round(($correctCount / $totalQuestions) * 100)
                    : 100;
                $attempt->score  = $finalScore;
                $attempt->passed = $finalScore >= $quiz->effective_passing_score;
                $attempt->save();

                if ($type === 'pre') {
                    // Setelah pre-test, enroll jadi active
                    $enrollment->status = 'active';
                    $enrollment->save();
                }
            });

            $msg = $type === 'pre'
                ? "Pre-test berhasil disimpan. Nilai Anda: {$finalScore}."
                : "Post-test berhasil disimpan. Nilai Anda: {$finalScore}.";

            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('status', 'test_submitted')
                ->with('score', $finalScore)
                ->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('error', 'Terjadi kesalahan saat menyimpan tes: ' . $e->getMessage());
        }
    }

    /**
     * Menangani submit Review (bintang) SETELAH Post-Test.
     * TANPA AJAX: form POST biasa, lalu redirect.
     */
    public function submitReview(Request $request, Course $course)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'stars' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('user.courses.show', $course->id)
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $course, $user) {
                $enrollment = Enrollment::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->firstOrFail();

                $enrollment->review_stars = $request->input('stars');
                // Anggap review dilakukan setelah selesai semua, tandai completed
                $enrollment->status       = 'completed';
                $enrollment->completed_at = now();
                $enrollment->save();
            });

            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('status', 'review_submitted')
                ->with('success', 'Ulasan Anda telah disimpan.');
        } catch (\Exception $e) {
            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('error', 'Terjadi kesalahan saat menyimpan ulasan: ' . $e->getMessage());
        }
    }
}
