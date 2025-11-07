<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\Lms\Course; // sementara tidak dipakai saat dummy

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Dummy data: daftar kursus milik user
        $courses = [
            [
                'id'        => 'course-web-1',
                'title'     => 'Fundamental Pemrograman Web',
                'category'  => 'WEB DEVELOPMENT',
                'instructor' => 'Pro Code Academy',
                'thumbnail' => 'https://picsum.photos/seed/webdev/800/400',
                'progress'  => 50,
                'done'      => '7/14 Modul',
                'cta'       => 'Lanjutkan Belajar',
                'cta_kind'  => 'primary',
                'status'    => 'in_progress',
            ],
            [
                'id'        => 'course-public-1',
                'title'     => 'Public Speaking untuk Pemula',
                'category'  => 'SOFT SKILLS',
                'instructor' => 'Trainer Profesional',
                'thumbnail' => 'https://picsum.photos/seed/public/800/400',
                'progress'  => 92,
                'done'      => '11/12 Modul',
                'cta'       => 'Selesaikan Ujian',
                'cta_kind'  => 'success',
                'status'    => 'in_progress',
            ],
            [
                'id'        => 'course-onboard-1',
                'title'     => 'Panduan Onboarding Karyawan Baru',
                'category'  => 'INTERNAL TRAINING',
                'instructor' => 'Divisi Pelatihan (Akses Private)',
                'thumbnail' => 'https://picsum.photos/seed/onboard/800/400',
                'progress'  => 0,
                'done'      => '0/5 Modul',
                'cta'       => 'Mulai Sekarang',
                'cta_kind'  => 'muted',
                'status'    => 'private',
            ],
            [
                'id'        => 'course-vue-3',
                'title'     => 'Mastering Vue 3: Composition API',
                'category'  => 'FRONT-END',
                'instructor' => 'Pro Code Kreator',
                'thumbnail' => 'https://picsum.photos/seed/vue3/800/400',
                'progress'  => 100,
                'done'      => '8/8 Modul',
                'cta'       => 'Lihat Sertifikat',
                'cta_kind'  => 'success',
                'status'    => 'completed',
            ],
            [
                'id'        => 'course-python-1',
                'title'     => 'Python Data Analysis Dasar',
                'category'  => 'DATA',
                'instructor' => 'Data School',
                'thumbnail' => 'https://picsum.photos/seed/python/800/400',
                'progress'  => 100,
                'done'      => '12/12 Modul',
                'cta'       => 'Ulangi Materi',
                'cta_kind'  => 'primary',
                'status'    => 'completed',
            ],
        ];

        // Tab aktif (default: in_progress)
        $activeTab = $request->string('tab')->toString() ?: 'in_progress';

        // Simple filtering untuk tab
        $filtered = array_values(array_filter($courses, fn($c) => $c['status'] === $activeTab));

        return view('user.courses.index', [
            'activeTab' => $activeTab,
            'courses'   => $filtered,
            'tabs'      => [
                'in_progress' => 'Sedang Dipelajari',
                'completed'   => 'Telah Selesai',
                'private'     => 'Kursus Private',
            ],
            // hitung jumlah per tab (untuk badge angka)
            'counts'    => [
                'in_progress' => count(array_filter($courses, fn($c) => $c['status'] === 'in_progress')),
                'completed'   => count(array_filter($courses, fn($c) => $c['status'] === 'completed')),
                'private'     => count(array_filter($courses, fn($c) => $c['status'] === 'private')),
            ],
        ]);
    }

    public function show(Request $request, string $courseId)
    {
        // Buat course dummy (sementara), atau ambil dari DB jika sudah ada
        $course = (object) [
            'id' => $courseId,
            'title' => 'Mastering Vue 3 (Dummy)',
            'subtitle' => 'Build UI modern dengan Composition API',
            'description' => 'Deskripsi singkat kursus dummy untuk pengujian.',
        ];

        $progress = [
            'percent' => 40,
            'last_lesson_id' => 'uuid-lesson-12',
        ];

        $modules = [
            [
                'id' => 'mod-1',
                'title' => 'Dasar-Dasar & Setup Vue',
                'lessons' => [
                    ['id' => 'l-1-1', 'title' => '1.1 Instalasi Node & NPM', 'type' => 'video', 'duration' => '5m', 'is_done' => true],
                    ['id' => 'l-1-2', 'title' => '1.2 Vue Instance & Lifecycle', 'type' => 'text',  'duration' => '10m', 'is_done' => true],
                ],
            ],
            [
                'id' => 'mod-2',
                'title' => 'Komponen & Props',
                'lessons' => [
                    ['id' => 'l-2-1', 'title' => '2.1 Quiz Komponen Dasar', 'type' => 'quiz', 'questions' => 5, 'is_done' => false],
                    ['id' => 'l-2-2', 'title' => '2.2 Props dan Event',      'type' => 'text', 'duration' => '12m', 'is_done' => false],
                ],
            ],
        ];

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

        return view('user.courses.show', compact(
            'course',
            'progress',
            'modules',
            'pretestResult',
            'posttestResult'
        ));
    }
}
