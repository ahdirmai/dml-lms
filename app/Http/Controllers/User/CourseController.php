<?php

namespace App\Http\Controllers\User; // Pastikan namespace Anda benar

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
     * Tampilkan daftar kursus yang terdaftar untuk user yang login.
     * (Versi dinamis untuk index.blade.php)
     */
    public function index(Request $request)
    {
        // ... (Fungsi index Anda tidak diubah) ...
        $user = Auth::user();
        $activeTab = $request->string('tab')->toString() ?: 'in_progress';

        $statusMap = [
            'in_progress' => ['assigned', 'active'],
            'completed'   => ['completed'],
            'private'     => [],
        ];

        $query = $user->enrollments()
            ->with('course', 'course.instructor')
            ->whereHas('course');

        if ($activeTab === 'private') {
            $query->whereHas('course', fn($q) => $q->where('status', 'private'));
        } else {
            $query->whereIn('status', $statusMap[$activeTab] ?? ['active']);
        }

        $enrollments = $query->get();

        $coursesData = $enrollments->map(function ($enrollment) use ($activeTab) {
            $course = $enrollment->course;
            $totalLessons = $course->lessons()->count();
            $completedLessons = $enrollment->lessonProgress()->completed()->count();
            $percent = ($totalLessons > 0) ? (int)(($completedLessons / $totalLessons) * 100) : 0;

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

            return [
                'id'        => $course->id,
                'title'     => $course->title,
                'category'  => $course->categories()->first()->name ?? 'Umum',
                'instructor' => $course->instructor->name ?? 'Internal',
                'thumbnail' => $course->thumbnail_path ?? 'https://picsum.photos/seed/' . $course->id . '/800/400',
                'progress'  => $percent,
                'done'      => "$completedLessons/$totalLessons Pelajaran",
                'cta'       => $cta,
                'cta_kind'  => $cta_kind,
                'status'    => $activeTab,
            ];
        });

        $counts = [
            'in_progress' => $user->enrollments()->whereIn('status', ['assigned', 'active'])->count(),
            'completed'   => $user->enrollments()->where('status', 'completed')->count(),
            'private'     => $user->enrollments()->whereHas('course', fn($q) => $q->where('status', 'private'))->count(),
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
     * Tampilkan detail kursus, modul, dan progres user.
     * (Versi dinamis untuk show.blade.php)
     *
     * *** DIPERBARUI DENGAN LOGIKA PRE-TEST GATE & DATA TES NYATA ***
     */
    public function show(Request $request, string $courseId)
    {
        // 1. Ambil Objek Inti
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Ambil enrollment user, DAN semua relasi yang diperlukan
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->with([
                // Load course & relasinya (modul, lesson, quiz, pre/post test)
                'course' => fn($q) => $q->with([
                    'modules' => fn($q_mod) => $q_mod->orderBy('order', 'asc'),
                    'modules.lessons' => fn($q_less) => $q_less->with('quiz')->orderBy('order_no', 'asc'),
                    'pretest', // Load definisi pretest
                    'posttest' // Load definisi posttest
                ]),
                'lessonProgress',         // Load semua progres pelajaran
                'latestPretestAttempt',   // Load attempt pretest terakhir
                'latestPosttestAttempt'   // Load attempt posttest terakhir
            ])
            ->first();

        if (!$enrollment) {
            // Jika user tidak terdaftar, tampilkan halaman 403
            abort(403, 'Anda tidak terdaftar pada kursus ini.');
        }

        $course = $enrollment->course; // Dapatkan objek course dari enrollment

        // 2. LOGIKA BARU: Pengecekan Pre-test Gate
        $pretestGateActive = false; // Flag untuk mengunci lesson
        $latestPretestAttempt = $enrollment->latestPretestAttempt;

        if ($course->require_pretest_before_content && $course->pretest) {
            if (!$latestPretestAttempt || !$latestPretestAttempt->passed) {
                // Jika pretest wajib, dan user belum ambil ATAU belum lulus,
                // aktifkan gerbang (kunci semua lesson)
                $pretestGateActive = true;
            }
        }

        // 3. Ambil Data Progres Pelajaran
        $completedLessonIds = $enrollment->lessonProgress
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->flip(); // Hasil: [ 'id-lesson-1' => 0, ... ]

        // 4. Siapkan Array Modul & Lesson (sesuai format view)
        $totalLessons = 0;
        $completedLessonCount = 0;

        // Gunakan map untuk mengubah koleksi Eloquent menjadi array
        // PERBARUI: Kirim $pretestGateActive ke dalam map
        $modules = $course->modules->map(function ($module) use ($completedLessonIds, &$totalLessons, &$completedLessonCount, $pretestGateActive) {

            $lessons = $module->lessons->map(function ($lesson) use ($completedLessonIds, &$completedLessonCount, $pretestGateActive) {
                $isDone = isset($completedLessonIds[$lesson->id]);
                if ($isDone) {
                    $completedLessonCount++;
                }

                $isQuiz = $lesson->kind === 'quiz' && $lesson->quiz;

                return [
                    'id'        => $lesson->id,
                    'title'     => $lesson->title,
                    'type'      => $lesson->kind, // 'video', 'text', 'quiz'
                    'duration'  => $lesson->duration_minutes ? $lesson->duration_minutes . 'm' : '-',
                    'is_done'   => $isDone,
                    'is_locked' => $pretestGateActive, // LOGIKA BARU DITAMBAHKAN
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

        // 5. Hitung Progres Keseluruhan
        $percent = ($totalLessons > 0) ? (int)(($completedLessonCount / $totalLessons) * 100) : 0;

        $lastProgress = $enrollment->lessonProgress->sortByDesc('last_activity_at')->first();

        $progress = [
            'percent'        => $percent,
            'last_lesson_id' => $lastProgress ? $lastProgress->lesson_id : null,
        ];

        // 6. Hasil Pre-test & Post-test (DATA NYATA)
        // Menggunakan helper baru di bawah
        $pretestResult = $this->formatTestResult($latestPretestAttempt, 'pre');
        $posttestResult = $this->formatTestResult($enrollment->latestPosttestAttempt, 'post');


        // 7. Kirim data dinamis ke view
        return view('user.courses.show', compact(
            'course',
            'progress',
            'modules',
            'pretestResult',    // Sekarang data nyata
            'posttestResult',   // Sekarang data nyata
            'pretestGateActive' // Flag baru untuk view
        ));
    }

    /**
     * HELPER BARU
     * Format hasil attempt kuis untuk ditampilkan di view.
     */
    private function formatTestResult(?QuizAttempt $attempt, string $type): ?array
    {
        if (!$attempt) {
            return null;
        }

        $score = (int) $attempt->score; // Ubah ke integer
        $badge = '';
        $desc = '';

        if ($attempt->passed) {
            $badge = ($type === 'pre') ? 'Pemahaman Awal Baik' : 'Lulus';
            $desc = "Anda lulus $type-test dengan nilai $score.";
        } else {
            $badge = 'Perlu Peningkatan';
            $desc = "Nilai $type-test Anda ($score) belum memenuhi skor minimum.";
            if ($type === 'pre') {
                $desc .= ' Selesaikan pre-test untuk membuka materi.';
            }
        }

        return [
            'score' => $score,
            'total' => 100,
            'date'  => $attempt->finished_at->format('d M Y'),
            'badge' => $badge,
            'desc'  => $desc,
        ];
    }


    /**
     * Menangani submit Pre-Test atau Post-Test dari modal.
     */
    public function submitTest(Request $request, Course $course, $type)
    {
        // ... (Fungsi submitTest Anda tidak diubah) ...
        $user = Auth::user();
        $finalScore = 0;
        $redirectUrl = null;

        if (!in_array($type, ['pre', 'post'])) {
            return response()->json(['success' => false, 'message' => 'Jenis tes tidak valid.'], 400);
        }

        try {
            DB::transaction(function () use ($request, $course, $type, $user, &$finalScore, &$redirectUrl) {

                $enrollment = Enrollment::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->firstOrFail();

                $quiz = ($type === 'pre') ? $course->pretest : $course->posttest;

                if (!$quiz) {
                    throw new \Exception('Kuis tidak ditemukan untuk kursus ini.');
                }

                $questions = $quiz->questions()->with('options')->get();
                $userAnswers = json_decode($request->input('answers', '{}'), true);

                $totalQuestions = $questions->count();
                $correctCount = 0;
                $now = now();

                $attempt = QuizAttempt::create([
                    'id' => (string) Str::uuid(),
                    'quiz_id' => $quiz->id,
                    'user_id' => $user->id,
                    'attempt_no' => 1, // TODO: Implementasi increment
                    'started_at' => $now,
                    'finished_at' => $now,
                    'score' => 0,
                    'passed' => false,
                ]);

                $answersToCreate = [];

                foreach ($questions as $index => $question) {
                    $answerKey = "q_$index";
                    $selectedOptionIndex = $userAnswers[$answerKey] ?? null;

                    $selectedOption = ($selectedOptionIndex !== null)
                        ? $question->options->get($selectedOptionIndex)
                        : null;

                    $isCorrect = $selectedOption && $selectedOption->is_correct;

                    if ($isCorrect) {
                        $correctCount++;
                    }

                    $answersToCreate[] = new QuizAnswer([
                        'id' => (string) Str::uuid(),
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'selected_option_id' => $selectedOption?->id,
                        'is_correct' => $isCorrect,
                        'score_awarded' => $isCorrect ? ($question->score ?? 1) : 0,
                    ]);
                }

                $attempt->answers()->saveMany($answersToCreate);

                $finalScore = ($totalQuestions > 0) ? round(($correctCount / $totalQuestions) * 100) : 100;

                $attempt->score = $finalScore;
                $attempt->passed = $finalScore >= $quiz->effective_passing_score;
                $attempt->save();

                if ($type === 'pre') {
                    $enrollment->status = 'active'; // Menggunakan status 'active'
                    $enrollment->save();
                    $redirectUrl = route('user.courses.show', $course->id);
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tes berhasil disimpan!',
            'score' => $finalScore,
            'redirectUrl' => $redirectUrl,
        ]);
    }

    /**
     * Menangani submit Review (bintang) setelah Post-Test.
     */
    public function submitReview(Request $request, Course $course)
    {
        // ... (Fungsi submitReview Anda tidak diubah) ...
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'stars' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $course, $user) {
                $enrollment = Enrollment::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->firstOrFail();

                // Asumsi ada kolom 'review_stars' di tabel enrollments
                $enrollment->review_stars = $request->input('stars');
                $enrollment->status = 'completed';
                $enrollment->completed_at = now();
                $enrollment->save();
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ulasan Anda telah disimpan.',
            'redirectUrl' => route('user.courses.show', $course->id) . '?review=success'
        ]);
    }
}
