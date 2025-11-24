{{-- resources/views/user/courses/show.blade.php --}}
<x-app-layout :title="($course->title ?? 'Detail Kursus')">
    <x-slot name="header">
        {{ __('Detail Kursus') }}
    </x-slot>

    @php
    // ==== Normalisasi & helper kecil ====
    $pct = (int)($progress['percent'] ?? 0);
    $lastLessonId = $progress['last_lesson_id'] ?? null;

    // Warna progress bar: merah -> kuning -> hijau
    $barColor = $pct >= 80
    ? 'bg-emerald-500'
    : ($pct >= 40 ? 'bg-amber-500' : 'bg-rose-500');

    // Status kursus dari progress
    $status = $pct >= 100 ? 'Completed' : ($pct > 0 ? 'In Progress' : 'Not Started');

    $statusStyles = [
    'Completed' => ['badge' => 'text-emerald-600 bg-emerald-50', 'text' => 'text-emerald-600'],
    'In Progress' => ['badge' => 'text-amber-600 bg-amber-50', 'text' => 'text-amber-600'],
    'Not Started' => ['badge' => 'text-gray-600 bg-gray-100', 'text' => 'text-gray-600'],
    ];
    $statusBadgeClass = $statusStyles[$status]['badge'] ?? 'text-gray-600 bg-gray-100';
    $statusTextClass = $statusStyles[$status]['text'] ?? 'text-gray-600';

    // Hitung total modul & total lesson
    $totalModules = count($modules ?? []);
    $totalLessons = collect($modules ?? [])->sum(fn($m) => count($m['lessons'] ?? []));

    // CTA utama
    $ctaHref = $lastLessonId
    ? route('user.lessons.show', $lastLessonId)
    : (isset($modules[0]['lessons'][0]['id'])
    ? route('user.lessons.show', $modules[0]['lessons'][0]['id'])
    : '#');

    // CTA label yang lebih friendly
    if ($pct >= 100) {
        $ctaLabel = 'Lihat Materi Kursus';
    } elseif ($lastLessonId) {
        $ctaLabel = 'Lanjutkan Pelajaran Terakhir';
    } else {
        $ctaLabel = 'Mulai Belajar';
    }

    // Dummy meta
    $ratingText = '⭐ 4.9 (1.2k review)';
    $author = $course->instructor->name ?? 'Internal Trainer';

    // Pre/Post test aman default
    $pre = $pretestResult ?? ['score'=>0,'total'=>100,'date'=>'-','badge'=>'Belum dikerjakan','desc'=>'Anda belum
    mengerjakan pre-test.'];
    $post = $posttestResult ?? ['score'=>0,'total'=>100,'date'=>'-','badge'=>'Belum dikerjakan','desc'=>'Anda belum
    mengerjakan post-test.'];

    // Flag gate pretest dari controller (default false kalau tidak ada)
    $pretestGateActive = $pretestGateActive ?? false;

    // ==== INFO UNTUK TOMBOL PRE/POST/REVIEW DI SAMPING CTA ====
    $courseId = $course->id;
    $hasPreTest = (bool) $course->pretest;
    $hasPostTest = (bool) $course->posttest;

    $preDone = !empty($pretestResult); // sudah punya attempt pretest
    $postDone = !empty($posttestResult); // sudah punya attempt posttest

    // aturan sederhana: boleh review kalau sudah ada post-test
    $canReview = $postDone;
    @endphp
    <div class="bg-soft/60">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-0 py-6 sm:py-8 lg:py-10 space-y-6 lg:space-y-8">

            {{-- FLASH MESSAGE --}}
            @if(session('success') || session('error'))
            <div class="mb-4">
                @if(session('success'))
                <div
                    class="flex items-start gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Berhasil</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
                @endif
                @if(session('error'))
                <div
                    class="mt-2 flex items-start gap-3 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M12 3a9 9 0 100 18 9 9 0 000-18z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Terjadi Kesalahan</p>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- BREADCRUMB --}}
            <nav class="text-xs sm:text-sm text-gray-500 flex items-center gap-1.5 sm:gap-2">
                <a href="{{ route('user.dashboard') }}" class="hover:text-brand hover:underline whitespace-nowrap">
                    Dashboard
                </a>
                <span>/</span>
                <a href="{{ route('user.courses.index') }}" class="hover:text-brand hover:underline whitespace-nowrap">
                    Kursus Saya
                </a>
                <span>/</span>
                <span class="font-semibold text-gray-700 whitespace-nowrap truncate">
                    {{ $course->title ?? 'Detail Kursus' }}
                </span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.7fr),minmax(0,1fr)] gap-6 lg:gap-8 items-start">

                {{-- ===================== KOLUMN KIRI ===================== --}}
                <div class="space-y-6">

                    {{-- Header kursus (judul, subtitle, meta) --}}
                    @include('user.courses.partials.header', [
                    'course' => $course,
                    'ratingText' => $ratingText,
                    'author' => $author,
                    'totalModules' => $totalModules,
                    'totalLessons' => $totalLessons,
                    ])

                    {{-- Status / Progress ringkas --}}
                    {{-- Status / Progress ringkas + tombol Pre/Post/Review --}}
                    @include('user.courses.partials.progress-summary', [
                    'pct' => $pct,
                    'barColor' => $barColor,
                    'status' => $status,
                    'statusBadgeClass' => $statusBadgeClass,
                    'ctaHref' => $ctaHref,
                    'ctaLabel' => $ctaLabel,
                    'totalModules' => $totalModules,
                    'pretestGateActive'=> $pretestGateActive,
                    'requirePretest' => $course->require_pretest_before_content,

                    // tambahan untuk tombol test di samping CTA
                    'courseId' => $courseId,
                    'hasPreTest' => $hasPreTest,
                    'hasPostTest' => $hasPostTest,
                    'preDone' => $preDone,
                    'postDone' => $postDone,
                    'canReview' => $canReview,

                    // Access Control
                    'isAccessBlocked' => $isAccessBlocked ?? false,
                    'accessMessage' => $accessMessage ?? null,

                    // Review Status
                    'reviewStars' => $reviewStars ?? null,
                    'hasReviewed' => !is_null($reviewStars ?? null),
                    ])

                    {{-- Banner info kalau pretest gate aktif --}}
                    @if($pretestGateActive)
                    <div
                        class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex gap-3">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 18.5A6.5 6.5 0 1012 5.5a6.5 6.5 0 000 13z" />
                        </svg>
                        <div>
                            <p class="font-semibold mb-0.5">Pre-Test Wajib Diselesaikan</p>
                            <p class="text-xs sm:text-sm">
                                Anda perlu menyelesaikan pre-test terlebih dahulu sebelum mengakses materi kursus ini.
                                Silakan kembali ke halaman utama kursus untuk memulai pre-test.
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- Tentang kursus --}}
                    @include('user.courses.partials.description', [
                    'course' => $course,
                    ])

                    {{-- Kurikulum / modul --}}
                    @include('user.courses.partials.curriculum', [
                    'modules' => $modules,
                    'pretestGateActive' => $pretestGateActive,
                    'isAccessBlocked' => $isAccessBlocked ?? false,
                    ])

                    {{-- Pretest & Posttest --}}
                    @include('user.courses.partials.evaluations', [
                    'pre' => $pre,
                    'post' => $post,
                    ])
                </div>

                {{-- ===================== KOLUMN KANAN (SIDEBAR STATUS) ===================== --}}
                @include('user.courses.partials.sidebar', [
                'pct' => $pct,
                'barColor' => $barColor,
                'status' => $status,
                'statusBadgeClass' => $statusBadgeClass,
                'statusTextClass' => $statusTextClass,
                'totalModules' => $totalModules,
                'ctaHref' => $ctaHref,
                'ctaLabel' => $ctaLabel,

                // penting: gate pretest
                'pretestGateActive'=> $pretestGateActive,

                // info kursus / quick actions (opsional)
                'course' => $course,
                'courseId' => $course->id,
                'hasPreTest' => (bool) $course->pretest,
                'hasPostTest' => (bool) $course->posttest,
                'preDone' => !empty($pretestResult),
                'postDone' => !empty($posttestResult),
                'preScore' => $pretestResult['score'] ?? 0,
                'postScore' => $posttestResult['score'] ?? 0,
                'canReview' => !empty($posttestResult),

                // Access Control
                'isAccessBlocked' => $isAccessBlocked ?? false,
                'accessMessage' => $accessMessage ?? null,

                // Review Status
                'reviewStars' => $reviewStars ?? null,
                'hasReviewed' => !is_null($reviewStars ?? null),
                ])
            </div>
        </div>
    </div>
    <x-test.modals :courses="$testCourses" />

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const openParam = urlParams.get('open');
            
            if (openParam === 'posttest') {
                // Remove param from URL without refresh
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
                
                // Trigger modal
                setTimeout(() => {
                    if (window.TestFlow && window.TestFlow.startPostTest) {
                        window.TestFlow.startPostTest('{{ $course->id }}');
                    }
                }, 500);
            }
        });
    </script>
    @endpush
</x-app-layout>