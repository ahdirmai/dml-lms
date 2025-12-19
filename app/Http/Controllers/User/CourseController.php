<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\QuizAnswer;
use App\Models\Lms\QuizAttempt;
use App\Models\UserActivityLog;
use App\Services\UserCourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    protected $userCourseService;

    public function __construct(UserCourseService $userCourseService)
    {
        $this->userCourseService = $userCourseService;
    }

    /**
     * Halaman "Kursus Saya"
     * - Data kursus + progress
     * - Tambahan: data pretest/posttest + URL submit (untuk modal)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeTab = $request->string('tab')->toString() ?: 'in_progress';

        $statusMap = [
            'in_progress' => ['assigned', 'active'],
            'completed' => ['completed'],
            'private' => [], // di-handle terpisah
        ];

        $allEnrollments = $this->userCourseService->getUserEnrollments($user->id);

        $filteredEnrollments = $allEnrollments;

        if ($activeTab === 'private') {
            $filteredEnrollments = $allEnrollments->filter(fn ($e) => $e->course && $e->course->status === 'private');
        } else {
            $filteredEnrollments = $allEnrollments->whereIn('status', $statusMap[$activeTab] ?? ['active']);
        }

        $coursesData = $this->userCourseService->formatForCourseList($filteredEnrollments, $activeTab);

        $counts = [
            'in_progress' => $allEnrollments->whereIn('status', ['assigned', 'active'])->count(),
            'completed' => $allEnrollments->where('status', 'completed')->count(),
            'private' => $allEnrollments->filter(fn ($e) => $e->course && $e->course->status === 'private')->count(),
        ];

        return view('user.courses.index', [
            'activeTab' => $activeTab,
            'courses' => $coursesData,
            'tabs' => [
                'in_progress' => 'Sedang Dipelajari',
                'completed' => 'Telah Selesai',
                'private' => 'Kursus Private',
            ],
            'counts' => $counts,
        ]);
    }

    /**
     * Detail kursus, modul, progress, dan hasil tes.
     */
    public function show(Request $request, string $courseId)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $enrollment = $this->userCourseService->getCourseEnrollmentDetails($user->id, $courseId);

        if (! $enrollment) {
            abort(403, 'Anda tidak terdaftar pada kursus ini.');
        }

        $data = $this->userCourseService->formatCourseDetails($enrollment);

        // Check Due Date Access
        $course = $enrollment->course;
        $isAccessBlocked = false;
        $accessMessage = null;

        if ($course->using_due_date) {
            $dueDate = $enrollment->dueDate;
            $now = now();

            if ($dueDate) {
                if ($dueDate->start_date && $now->lt($dueDate->start_date)) {
                    $isAccessBlocked = true;
                    $accessMessage = 'Kursus belum dimulai. Akses dibuka pada ' . \Carbon\Carbon::parse($dueDate->start_date)->format('d M Y');
                } elseif ($dueDate->end_date && $now->gt($dueDate->end_date)) {
                    $isAccessBlocked = true;
                    $accessMessage = 'Masa akses kursus telah berakhir pada ' . \Carbon\Carbon::parse($dueDate->end_date)->format('d M Y');
                }
            }
        }

        $data['isAccessBlocked'] = $isAccessBlocked;
        $data['accessMessage'] = $accessMessage;

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'view_course',
            'subject_type' => Course::class,
            'subject_id' => $course->id,
            'description' => "Viewed course: {$course->title}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('user.courses.show', $data);
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

        if (! in_array($type, ['pre', 'post'], true)) {
            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('error', 'Jenis tes tidak valid.');
        }

        $finalScore = 0; // Initialize finalScore outside the transaction scope

        DB::beginTransaction(); // Start the transaction
        try {
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->firstOrFail();

            $quiz = ($type === 'pre') ? $course->pretest : $course->posttest;
            if (! $quiz) {
                throw new \Exception('Kuis tidak ditemukan untuk kursus ini.');
            }

            // return $quiz;

            $questions = $quiz->questions()->with('options')->get();

            $answers = $request->input('answers', []); // array: index => optionIndex
            $totalQuestions = $questions->count();
            $correctCount = 0;
            $now = now();

            $attemptNo = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->max('attempt_no') ?? 0;
            $attemptNo++;

            $attempt = QuizAttempt::create([
                'id' => (string) Str::uuid(),
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'attempt_no' => $attemptNo,
                'started_at' => $now,
                'finished_at' => $now,
                'score' => 0,
                'passed' => false,
            ]);

            // return $attempt;

            $answersToCreate = [];

            $totalMaxScore = 0;
            $totalScoreAwarded = 0;

            foreach ($questions as $index => $question) {

                $options = $question->options->values();

                // return $options;
                $selectedIndex = $answers[$index] ?? null;

                $selectedOption = null;
                if ($selectedIndex !== null && isset($options[$selectedIndex])) {
                    $selectedOption = $options[$selectedIndex];
                }

                // return $selectedOption;

                $isCorrect = $selectedOption && $selectedOption->is_correct;

                if ($isCorrect) {
                    $correctCount++;
                }
                
                $questionScore = $question->score ?? 1;
                $totalMaxScore += $questionScore;
                $scoreAwarded = $isCorrect ? $questionScore : 0;
                $totalScoreAwarded += $scoreAwarded;

                $answersToCreate[] = new QuizAnswer([
                    'id' => (string) Str::uuid(),
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_option_id' => $selectedOption?->id,
                    'is_correct' => $isCorrect,
                    'score_awarded' => $scoreAwarded,
                ]);
            }

            // return $answersToCreate;

            $attempt->answers()->saveMany($answersToCreate);

            $finalScore = ($totalMaxScore > 0)
                ? round(($totalScoreAwarded / $totalMaxScore) * 100)
                : 100;
            $attempt->score = $finalScore;
            $attempt->passed = $finalScore >= $quiz->effective_passing_score;
            $attempt->save();

            // return $attempt;

            if ($type === 'pre') {
                if ($course->require_pretest_before_content) {
                    if ($attempt->passed) {
                        $enrollment->status = 'active';
                        $enrollment->save();
                        $this->userCourseService->initializeLessonProgress($enrollment);
                    }
                } else {
                    $enrollment->status = 'active';
                    $enrollment->save();
                    $this->userCourseService->initializeLessonProgress($enrollment);
                }
            }

            DB::commit(); // Commit the transaction
            
            // Log activity
            UserActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => $type === 'pre' ? 'submit_pretest' : 'submit_posttest',
                'subject_type' => Course::class,
                'subject_id' => $course->id,
                'description' => ($type === 'pre' ? 'Submitted pretest' : 'Submitted posttest') . " for course: {$course->title}. Score: {$finalScore}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            $msg = $type === 'pre'
                ? "Pre-test berhasil disimpan. Nilai Anda: {$finalScore}."
                : "Post-test berhasil disimpan. Nilai Anda: {$finalScore}.";

            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('status', 'test_submitted')
                ->with('score', $finalScore)
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on error

            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('error', 'Terjadi kesalahan saat menyimpan tes: '.$e->getMessage());
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

        // --- ADDED: Check Passing Logic ---
        // Jika course punya posttest, user HARUS PASSED posttest dulu.
        if ($course->has_posttest) {
            if ($course->posttest) {
                // Cek apakah pernah lulus
                $hasPassed = QuizAttempt::where('quiz_id', $course->posttest->id)
                    ->where('user_id', $user->id)
                    ->where('passed', true)
                    ->exists();

                if (! $hasPassed) {
                    return redirect()
                        ->route('user.courses.show', $course->id)
                        ->with('error', 'Anda belum lulus Post Test. Silakan selesaikan Post Test dengan nilai di atas KKM sebelum memberikan ulasan.');
                }
            } else {
                // Edge case: flag has_posttest true, tapi datanya null (error data).
                // Log and allow? Or block? Better block to be safe or maybe allow if tech issue.
                // Let's allow but log, or simply ignore check.
                // For now, let's assume if data is missing, we skip check.
            }
        }
        // Jika TIDAK punya posttest, mungkin logic lain (misal semua lesson complete).
        // Tapi request user spesifik: "Check Passing Grade".
        // ----------------------------------

        try {
            DB::transaction(function () use ($request, $course, $user) {
                $enrollment = Enrollment::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->firstOrFail();

                $enrollment->review_stars = $request->input('stars');
                
                // Hanya set completed jika belum completed (agar timestamp tidak berubah terus)
                if ($enrollment->status !== 'completed') {
                    $enrollment->status = 'completed';
                    $enrollment->completed_at = now();
                }
                
                $enrollment->save();
            });

            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('status', 'review_submitted')
                ->with('success', 'Ulasan Anda telah disimpan.');
        } catch (\Exception $e) {
            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('error', 'Terjadi kesalahan saat menyimpan ulasan: '.$e->getMessage());
        }
    }

    /**
     * Generate and stream Certificate PDF.
     */
    public function certificate(Request $request, Course $course)
    {
        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        // 1. Cek apakah sudah review (syarat sertifikat)
        if (! $enrollment->review_stars) {
            return redirect()
                ->route('user.courses.show', $course->id)
                ->with('error', 'Anda harus memberikan ulasan sebelum mengunduh sertifikat.');
        }

        // 2. Cek atau Buat Sertifikat
        $certificate = $enrollment->certificate;
        if (! $certificate) {
            // Generate nomor unik: DML-YYYYMMDD-RANDOM
            $dateStr = now()->format('Ymd');
            $uniqueStr = strtoupper(Str::random(6));
            $certNum = "DML-{$dateStr}-{$uniqueStr}";

            $certificate = \App\Models\Lms\Certificate::create([
                'enrollment_id' => $enrollment->id,
                'certificate_number' => $certNum,
                'issued_at' => now(),
            ]);
        }

        // 3. Siapkan Data View
        // Load modules for syllabus page
        $course->load(['modules.lessons']);

        $data = [
            'user' => $user,
            'course' => $course,
            'certificate' => $certificate,
            'date' => $certificate->issued_at->format('d F Y'),
        ];

        // 4. Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('user.certificates.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream("Sertifikat-{$course->title}.pdf");
    }
}
