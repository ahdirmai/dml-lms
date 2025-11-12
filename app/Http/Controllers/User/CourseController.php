<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- 1. Tambahkan ini
use App\Models\Lms\Course;            // <-- 2. Tambahkan ini
use App\Models\Lms\Enrollment;        // <-- 3. Tambahkan ini
use App\Models\Lms\LessonProgress;    // <-- 4. Tambahkan ini

class CourseController extends Controller
{
    /**
     * Tampilkan daftar kursus yang terdaftar untuk user yang login.
     * (Versi dinamis untuk index.blade.php)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeTab = $request->string('tab')->toString() ?: 'in_progress';

        // Tentukan status enrollment berdasarkan tab
        // 'in_progress' bisa mencakup 'assigned' (belum mulai) dan 'active' (sedang berjalan)
        $statusMap = [
            'in_progress' => ['assigned', 'active'],
            'completed'   => ['completed'],
            'private'     => [], // Logika 'private' mungkin perlu penyesuaian
        ];

        // Ambil enrollments user, filter berdasarkan status di tab
        $query = $user->enrollments()
            ->with('course', 'course.instructor') // Eager load relasi course
            ->whereHas('course'); // Pastikan course masih ada

        if ($activeTab === 'private') {
            // Asumsi 'private' adalah status di course, bukan enrollment
            $query->whereHas('course', fn($q) => $q->where('status', 'private'));
        } else {
            $query->whereIn('status', $statusMap[$activeTab] ?? ['active']);
        }

        $enrollments = $query->get();

        // 1. Hitung progres untuk setiap enrollment
        // Ini bisa menjadi query N+1, pertimbangkan optimasi jika berat
        $coursesData = $enrollments->map(function ($enrollment) use ($activeTab) {
            $course = $enrollment->course;

            // Hitung progres
            $totalLessons = $course->lessons()->count();
            $completedLessons = $enrollment->lessonProgress()->completed()->count();
            $percent = ($totalLessons > 0) ? (int)(($completedLessons / $totalLessons) * 100) : 0;

            $cta = 'Lihat Kursus';
            $cta_kind = 'primary';
            if ($enrollment->status === 'completed') {
                $cta = 'Lihat Sertifikat'; // Ganti logikanya
                $cta_kind = 'success';
            } elseif ($percent > 0) {
                $cta = 'Lanjutkan Belajar';
            } else {
                $cta = 'Mulai Belajar';
            }

            return [
                'id'        => $course->id,
                'title'     => $course->title,
                // Ambil kategori pertama, atau ganti dengan logika Anda
                'category'  => $course->categories()->first()->name ?? 'Umum',
                'instructor' => $course->instructor->name ?? 'Internal',
                'thumbnail' => $course->thumbnail_path ?? 'https://picsum.photos/seed/' . $course->id . '/800/400',
                'progress'  => $percent,
                'done'      => "$completedLessons/$totalLessons Pelajaran",
                'cta'       => $cta,
                'cta_kind'  => $cta_kind,
                // Status untuk filtering tab
                'status'    => $activeTab,
            ];
        });

        // 2. Hitung total untuk badge tab
        $counts = [
            'in_progress' => $user->enrollments()->whereIn('status', ['assigned', 'active'])->count(),
            'completed'   => $user->enrollments()->where('status', 'completed')->count(),
            'private'     => $user->enrollments()->whereHas('course', fn($q) => $q->where('status', 'private'))->count(),
        ];

        return view('user.courses.index', [
            'activeTab' => $activeTab,
            'courses'   => $coursesData, // Data dinamis
            'tabs'      => [
                'in_progress' => 'Sedang Dipelajari',
                'completed'   => 'Telah Selesai',
                'private'     => 'Kursus Private', // Anda bisa ganti/hapus tab ini
            ],
            'counts'    => $counts, // Count dinamis
        ]);
    }


    /**
     * Tampilkan detail kursus, modul, dan progres user.
     * (Versi dinamis untuk show.blade.php)
     */
    public function show(Request $request, string $courseId)
    {
        // 1. Ambil Objek Inti
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Ambil course DAN relasi modul + lessons (diurutkan)
        $course = Course::with([
            'modules' => fn($q) => $q->orderBy('order', 'asc'),
            'modules.lessons' => fn($q) => $q->with('quiz')->orderBy('order_no', 'asc'),
            // 'pretest', 'posttest' // Diperlukan untuk Pre/Post test dinamis
        ])->findOrFail($courseId);

        // Ambil data pendaftaran (enrollment) user di kursus ini
        $enrollment = $course->enrollments()
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            // Jika user tidak terdaftar, tampilkan halaman 403
            abort(403, 'Anda tidak terdaftar pada kursus ini.');
        }

        // 2. Ambil Data Progres Pelajaran
        // Ambil semua ID lesson yang sudah selesai untuk enrollment ini
        // Kita "flip" arraynya agar bisa dicek dengan `isset()` (lebih cepat)
        $completedLessonIds = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->flip(); // Hasil: [ 'id-lesson-1' => 0, 'id-lesson-2' => 1, ... ]

        // 3. Siapkan Array Modul & Lesson (sesuai format view)
        $totalLessons = 0;
        $completedLessonCount = 0;

        // Kita gunakan map untuk mengubah koleksi Eloquent menjadi array
        $modules = $course->modules->map(function ($module) use ($completedLessonIds, &$totalLessons, &$completedLessonCount) {

            $lessons = $module->lessons->map(function ($lesson) use ($completedLessonIds, &$completedLessonCount) {
                // Cek apakah ID lesson ini ada di daftar yang sudah selesai
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
                    // Logika 'questions' bergantung pada relasi 'quiz' Anda
                    // Asumsi: $lesson->quiz->questions()->count() atau ada kolom 'question_count'
                    'questions' => $isQuiz ? ($lesson->quiz->questions_count ?? 0) : 0,
                ];
            });

            // Akumulasi total pelajaran
            $totalLessons += $lessons->count();

            return [
                'id'      => $module->id,
                'title'   => $module->title,
                'lessons' => $lessons->all(), // Ubah koleksi lesson menjadi array
            ];
        });

        // 4. Hitung Progres Keseluruhan
        $percent = ($totalLessons > 0) ? (int)(($completedLessonCount / $totalLessons) * 100) : 0;

        // Cari aktivitas lesson terakhir user
        $lastProgress = LessonProgress::where('enrollment_id', $enrollment->id)
            ->orderBy('last_activity_at', 'desc')
            ->first();

        $progress = [
            'percent'        => $percent,
            'last_lesson_id' => $lastProgress ? $lastProgress->lesson_id : null,
        ];

        // 5. Hasil Pre-test & Post-test (MASIH DUMMY)
        // NOTE: Logika ini memerlukan model 'Quiz' dan 'QuizAttempt' yang terdefinisi penuh.
        // Ganti bagian ini dengan query Anda untuk mengambil hasil tes terbaik (best attempt)
        // dari user untuk pretest dan posttest kursus ini.

        // Contoh query (jika model sudah ada):
        // $pretestQuiz = $course->pretest;
        // $bestPretest = $pretestQuiz ? $pretestQuiz->attempts()->where('enrollment_id', $enrollment->id)->orderBy('score', 'desc')->first() : null;
        // $pretestResult = formatTestResult($bestPretest); // Buat helper function

        $pretestResult = [
            'score' => 62,
            'total' => 100,
            'date'  => now()->subDays(6)->format('d M Y'),
            'badge' => 'Perlu Pemanasan',
            'desc'  => 'Cukup mengenal dasar. Disarankan ulang materi modul 1.',
        ];
        $posttestResult = [
            'score' => 88,
            'total' => 100,
            'date'  => now()->format('d M Y'),
            'badge' => 'Siap Produksi',
            'desc'  => 'Pemahaman sudah matang. Lanjut proyek nyata.',
        ];


        // 6. Kirim data dinamis ke view
        return view('user.courses.show', compact(
            'course',           // Data course dari Eloquent
            'progress',         // Array progress yang sudah dihitung
            'modules',          // Array modules & lessons yang sudah diformat
            'pretestResult',    // Masih dummy, ganti dengan data dinamis
            'posttestResult'    // Masih dummy, ganti dengan data dinamis
        ));
    }
}
