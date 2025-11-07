<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Route yang direkomendasikan:
     * Route::get('/lessons/{lessonId}', [LessonController::class, 'show'])->name('user.lessons.show');
     *
     * NOTE:
     * - Kita TIDAK pakai model binding di sini, jadi parameter harus sama: {lessonId} -> $lessonId
     * - Sidebar & konten disiapkan dari dummy data yang konsisten (ada course_id, module_id, lesson id)
     */
    public function show(Request $request, string $lessonId)
    {
        // ===== Dummy Course =====
        $course = (object) [
            'id'          => 'course-uuid-1',
            'slug'        => 'mastering-vue-3',
            'title'       => 'Mastering Vue 3',
            'subtitle'    => 'Membangun UI modern dengan Composition API',
            'description' => 'Kursus komprehensif Vue 3 untuk pemula hingga siap produksi.',
        ];

        // ===== Dummy Modules & Lessons (lengkap dengan id kunci) =====
        $modules = [
            [
                'id'    => 'mod-1',
                'title' => 'Dasar-Dasar & Setup Vue',
                'lessons' => [
                    [
                        'id'         => 'video-uuid-1',
                        'course_id'  => $course->id,
                        'module_id'  => 'mod-1',
                        'title'      => '1.1 Instalasi Node & NPM (Video)',
                        'type'       => 'video',
                        'duration'   => '5m',
                        'is_done'    => true,
                    ],
                    [
                        'id'         => 'text-uuid-1',
                        'course_id'  => $course->id,
                        'module_id'  => 'mod-1',
                        'title'      => '1.2 Vue Instance & Lifecycle (Teks)',
                        'type'       => 'text',
                        'duration'   => '10m',
                        'is_done'    => true,
                    ],
                ],
            ],
            [
                'id'    => 'mod-2',
                'title' => 'Komponen & Props',
                'lessons' => [
                    [
                        'id'         => 'quiz-uuid-1',
                        'course_id'  => $course->id,
                        'module_id'  => 'mod-2',
                        'title'      => '2.1 Quiz Komponen Dasar',
                        'type'       => 'quiz',
                        'questions'  => 5,
                        'is_done'    => false,
                    ],
                    [
                        'id'         => 'text-uuid-2',
                        'course_id'  => $course->id,
                        'module_id'  => 'mod-2',
                        'title'      => '2.2 Props dan Event',
                        'type'       => 'text',
                        'duration'   => '12m',
                        'is_done'    => false,
                    ],
                ],
            ],
        ];

        // ===== Flatten untuk cari lesson aktif dari $lessonId =====
        $flat = [];
        foreach ($modules as $m) {
            foreach ($m['lessons'] as $ls) {
                $flat[] = $ls;
            }
        }

        // Index lesson saat ini
        $currIndex = collect($flat)->search(fn($l) => ($l['id'] ?? null) === $lessonId);
        if ($currIndex === false) {
            // fallback jika ID tidak ketemu → pakai lesson pertama
            $currIndex = 0;
        }
        $current = $flat[$currIndex];

        // Bentuk objek lesson untuk view (berisi id, course_id, module_id, title, type, meta opsional)
        $lesson = (object) [
            'id'        => $current['id'],
            'course_id' => $current['course_id'],
            'module_id' => $current['module_id'],
            'title'     => $current['title'],
            'type'      => $current['type'],
            'meta'      => $current['duration'] ?? (($current['questions'] ?? null) ? "{$current['questions']} pertanyaan" : null),
        ];

        // ===== Siapkan konten berdasarkan tipe lesson =====
        switch ($lesson->type) {
            case 'video':
                $content = [
                    'type'  => 'video',
                    'video' => [
                        'src'    => 'https://cdn.coverr.co/videos/coverr-programmer-typing-on-laptop-6715/1080p.mp4',
                        'poster' => 'https://images.unsplash.com/photo-1557800636-894a64c1696f?q=80&w=1920&auto=format&fit=crop',
                    ],
                    'body'  => 'Pada pelajaran ini Anda akan menyiapkan lingkungan pengembangan untuk Vue 3 dan memastikan Node & NPM terpasang dengan benar.',
                ];
                break;

            case 'text':
                $content = [
                    'type' => 'text',
                    'body' => [
                        'lead' => 'Lifecycle adalah tahapan hidup sebuah komponen dari diciptakan sampai dihancurkan.',
                        'html' => '<p>Kita akan mempelajari <strong>onMounted</strong>, <strong>onUpdated</strong>, dan <strong>onUnmounted</strong> dalam Composition API.</p>',
                        'code' => "import { ref, onMounted, onUnmounted } from 'vue'\n\nconst count = ref(0)\nlet timer\n\nonMounted(() => {\n  timer = setInterval(() => count.value++, 1000)\n})\n\nonUnmounted(() => {\n  clearInterval(timer)\n})",
                        'tips' => 'Gunakan onMounted untuk memanggil API pertama kali dan pastikan membersihkan efek di onUnmounted.',
                    ],
                ];
                break;

            case 'quiz':
                $content = [
                    'type'      => 'quiz',
                    'questions' => [
                        [
                            'q'       => 'Apa fungsi props pada komponen?',
                            'choices' => [
                                ['label' => 'Menyimpan state lokal'],
                                ['label' => 'Mengirim data dari parent ke child'],
                                ['label' => 'Mengubah DOM langsung'],
                            ],
                        ],
                        [
                            'q'       => 'Manakah cara yang tepat mengirim event dari child ke parent?',
                            'choices' => [
                                ['label' => 'Memodifikasi props langsung'],
                                ['label' => 'emit di child, ditangkap di parent'],
                                ['label' => 'Akses DOM parent dari child'],
                            ],
                        ],
                    ],
                ];
                break;

            default:
                $content = [
                    'type'  => 'text',
                    'body'  => [
                        'lead' => 'Konten belum tersedia.',
                        'html' => '<p>Silakan kembali nanti.</p>',
                    ],
                ];
        }

        // ===== Prev/Next lesson (berdasarkan flatten list) =====
        $prev = $currIndex > 0 ? $flat[$currIndex - 1] : null;
        $next = $currIndex < count($flat) - 1 ? $flat[$currIndex + 1] : null;

        $prevLesson = $prev ? (object) [
            'id'        => $prev['id'],
            'course_id' => $prev['course_id'],
            'module_id' => $prev['module_id'],
            'title'     => $prev['title'],
            'type'      => $prev['type'],
        ] : null;

        $nextLesson = $next ? (object) [
            'id'        => $next['id'],
            'course_id' => $next['course_id'],
            'module_id' => $next['module_id'],
            'title'     => $next['title'],
            'type'      => $next['type'],
        ] : null;

        // ===== Sidebar data (opsional alternatif) =====
        $sidebar = array_map(function ($m) {
            return [
                'label' => 'MODUL',
                'title' => $m['title'],
                'items' => array_map(function ($ls) {
                    return [
                        'id'    => $ls['id'],
                        'title' => $ls['title'],
                        'type'  => $ls['type'],
                        'is_done' => $ls['is_done'] ?? false,
                    ];
                }, $m['lessons']),
            ];
        }, $modules);

        return view('user.lessons.show', compact(
            'course',
            'modules',     // dipakai sidebar di view
            'sidebar',     // alternatif jika butuh
            'lesson',
            'content',
            'prevLesson',
            'nextLesson'
        ));
    }
}
