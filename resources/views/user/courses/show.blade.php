{{-- resources/views/user/courses/show.blade.php --}}
<x-app-layout :title="($course->title ?? 'Detail Kursus')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Detail Course') }}
        </h2>
    </x-slot>

    @php
    // ==== Normalisasi & helper kecil ====
    $pct = (int)($progress['percent'] ?? 0);
    $lastLessonId = $progress['last_lesson_id'] ?? null;

    // Warna progress bar: merah -> kuning -> hijau
    $barColor = $pct >= 80 ? 'bg-emerald-500' : ($pct >= 40 ? 'bg-amber-500' : 'bg-rose-500');

    // Status kursus dari progress
    $status = $pct >= 100 ? 'Completed' : ($pct > 0 ? 'In Progress' : 'Not Started');
    $statusColor = [
    'Completed' => 'text-emerald-600 bg-emerald-100',
    'In Progress' => 'text-amber-600 bg-amber-100',
    'Not Started' => 'text-gray-600 bg-gray-100',
    ][$status];

    // Hitung total modul & total lesson
    $totalModules = count($modules ?? []);
    $totalLessons = collect($modules ?? [])->sum(fn($m) => count($m['lessons'] ?? []));

    // CTA
    $ctaHref = $lastLessonId ? route('user.lessons.show', $lastLessonId)
    : (isset($modules[0]['lessons'][0]['id']) ? route('user.lessons.show', $modules[0]['lessons'][0]['id']) : '#');
    $ctaLabel = $lastLessonId ? 'Lanjutkan Pelajaran Terakhir' : 'Mulai Belajar';

    // Dummy meta rating
    $ratingText = '⭐ 4.9 (1.2k review)';
    $author = 'Pro Code Kreator';

    // Pre/Post test aman default
    $pre = $pretestResult ?? ['score'=>0,'total'=>100,'date'=>'-','badge'=>'—','desc'=>'—'];
    $post = $posttestResult ?? ['score'=>0,'total'=>100,'date'=>'-','badge'=>'—','desc'=>'—'];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-[1fr,22rem] gap-8">
        {{-- ===================== KOLUMN KIRI ===================== --}}
        <div>
            {{-- BREADCRUMB --}}
            <nav class="mb-6 text-sm text-gray-500">
                <a href="{{ route('user.dashboard') }}" class="hover:underline">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('user.dashboard') }}" class="hover:underline">Kursus Saya</a>
                <span class="mx-2">/</span>
                <span class="font-semibold text-gray-700">{{ $course->title ?? 'Detail Kursus' }}</span>
            </nav>

            {{-- JUDUL + SUBTITLE --}}
            <header class="mb-6">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">
                    {{ $course->title ?? 'Mastering Vue.js untuk Front-End Developer' }}
                </h1>
                <p class="text-lg text-gray-600 mt-1">
                    {{ $course->subtitle ?? 'Pelajari framework JavaScript modern untuk antarmuka yang cepat.' }}
                </p>
                <div class="flex items-center gap-4 text-sm text-gray-700 mt-4">
                    <span class="font-semibold text-yellow-600">{{ $ratingText }}</span>
                    <span>Oleh: {{ $author }}</span>
                    <span class="text-gray-400">•</span>
                    <span>{{ $totalModules }} Modul / {{ $totalLessons }} Pelajaran</span>
                </div>
            </header>

            {{-- STATUS / PROGRESS RINGKAS --}}
            <section class="bg-white p-5 md:p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg md:text-xl font-bold text-gray-900">Progres Anda</h3>
                    <span
                        class="inline-flex items-center gap-2 text-xs font-semibold px-2.5 py-1 rounded-lg {{ $statusColor }}">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-current"></span>{{ $status }}
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                    <div class="{{ $barColor }} h-3 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs text-gray-600">
                    <span>{{ $pct }}% Selesai</span>
                    <span>{{ $totalModules }} Modul</span>
                </div>

                <div class="mt-4">
                    <a href="{{ $ctaHref }}"
                        class="w-full inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl text-sm md:text-base transition shadow">
                        {{ $ctaLabel }}
                    </a>
                </div>
            </section>

            {{-- TENTANG KURSUS --}}
            <section class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-blue-600 mb-2">Tentang Kursus Ini</h3>
                <p class="text-gray-700 leading-relaxed">
                    {{ $course->description ?? 'Kursus ini adalah jalur cepat (bootcamp) untuk menguasai Vue 3. Anda
                    akan membangun 3 proyek nyata, memahami Composition API, state management, routing, dan praktik
                    terbaik produksi.' }}
                </p>
            </section>

            {{-- KURIKULUM / MODUL --}}
            <section class="mt-8">
                <h3 class="text-xl font-bold text-blue-600 mb-4">Daftar Modul</h3>

                @forelse($modules as $m)
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-800">{{ $m['title'] }}</span>
                        <span class="text-xs text-gray-500">{{ count($m['lessons'] ?? []) }} Pelajaran</span>
                    </div>
                    <ul class="mt-3 divide-y divide-gray-100">
                        @foreach($m['lessons'] as $ls)
                        @php
                        $done = (bool)($ls['is_done'] ?? false);
                        $isQuiz = ($ls['type'] ?? '') === 'quiz';
                        @endphp
                        <li class="py-2 flex items-center justify-between">
                            <div class="flex items-center gap-2 min-w-0">
                                @if($done)
                                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                @else
                                <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 7h16M4 12h16M4 17h10" />
                                </svg>
                                @endif

                                <a href="{{ route('user.lessons.show', $ls['id']) }}"
                                    class="text-sm font-semibold truncate {{ $done ? 'text-emerald-600' : 'text-gray-700 hover:text-blue-600' }}">
                                    {{ $ls['title'] }}
                                </a>
                            </div>

                            <span class="ml-3 text-xs {{ $isQuiz ? 'text-rose-600 font-semibold' : 'text-gray-500' }}">
                                @if($isQuiz)
                                {{ $ls['questions'] ?? 0 }} Pertanyaan
                                @else
                                {{ $ls['duration'] ?? '-' }}
                                @endif
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @empty
                <div class="bg-white p-6 rounded-xl border text-gray-500">Belum ada modul.</div>
                @endforelse
            </section>

            {{-- PRETEST & POSTTEST --}}
            <section class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- PRETEST --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-[11px] font-extrabold text-gray-500 tracking-wider">EVALUASI AWAL</p>
                    <h4 class="text-xl font-bold text-gray-900 mt-1">Pretest</h4>
                    <div class="mt-3 flex items-end gap-3">
                        <div>
                            <div class="text-4xl font-extrabold text-blue-600">
                                {{ $pre['score'] }}/{{ $pre['total'] }}
                            </div>
                            <div class="text-xs text-gray-500">Tanggal: {{ $pre['date'] }}</div>
                        </div>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-600">
                            {{ $pre['badge'] }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-gray-600">{{ $pre['desc'] }}</p>
                    <div class="mt-4 flex gap-2">
                        <a href="#"
                            class="px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm font-semibold text-gray-800">Lihat
                            Detail</a>
                        <a href="#"
                            class="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-sm font-semibold text-white">Ulang
                            Pretest</a>
                    </div>
                </div>

                {{-- POSTTEST --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-[11px] font-extrabold text-gray-500 tracking-wider">EVALUASI AKHIR</p>
                    <h4 class="text-xl font-bold text-gray-900 mt-1">Posttest</h4>
                    <div class="mt-3 flex items-end gap-3">
                        <div>
                            <div class="text-4xl font-extrabold text-emerald-600">
                                {{ $post['score'] }}/{{ $post['total'] }}
                            </div>
                            <div class="text-xs text-gray-500">Tanggal: {{ $post['date'] }}</div>
                        </div>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-600">
                            {{ $post['badge'] }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-gray-600">{{ $post['desc'] }}</p>
                    <div class="mt-4 flex gap-2">
                        <a href="#"
                            class="px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm font-semibold text-gray-800">Lihat
                            Detail</a>
                        <a href="#"
                            class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-sm font-semibold text-white">Mulai
                            Posttest</a>
                    </div>
                </div>
            </section>
        </div>

        {{-- ===================== KOLUMN KANAN (SIDEBAR STATUS) ===================== --}}
        <aside class="hidden lg:block">
            <div class="bg-white p-6 rounded-2xl shadow-xl sticky top-24 inline-block">
                {{-- STATUS --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full {{ $statusColor }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-current" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                @if($status==='Completed')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                                @elseif($status==='In Progress')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6l4 2" />
                                @else
                                <circle cx="12" cy="12" r="4" stroke-width="3"></circle>
                                @endif
                            </svg>
                        </span>
                        <p
                            class="text-sm font-semibold {{ str_contains($statusColor,'text-') ? explode(' ', $statusColor)[0] : 'text-gray-700' }}">
                            {{ $status }}
                        </p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600" title="Informasi status" aria-label="Info status">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 18.5A6.5 6.5 0 1012 5.5a6.5 6.5 0 000 13z" />
                        </svg>
                    </button>
                </div>

                {{-- INFORMASI PENUGASAN (dummy) --}}
                <div class="text-xs text-gray-600 space-y-1 mb-4">
                    <p><span class="font-semibold">Assigned on:</span> 20 Sep 2025</p>
                    <p><span class="font-semibold">Assigned by:</span> Operations Training Dept</p>
                    <p><span class="font-semibold">Last activity:</span> {{ now()->format('j M Y') }}</p>
                </div>

                <hr class="my-3">

                {{-- PROGRESS --}}
                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-700 mb-1">Progres Anda</p>
                    <p class="text-xs text-gray-500 mb-1">{{ $pct }}% Selesai ({{ $totalModules }} Modul)</p>
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="{{ $barColor }} h-2" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                <hr class="my-3">

                {{-- RIWAYAT SINGKAT --}}
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-2">Riwayat Singkat</p>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2">
                            <span
                                class="inline-block w-2.5 h-2.5 rounded-full {{ $pct >= 100 ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                            <span>{{ $pct >= 100 ? 'Completed Course' : 'Dalam Proses' }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-800"></span>
                            <span>Contoh: Watched Modules / Took tests</span>
                        </li>
                    </ul>
                </div>

                {{-- CTA CEPAT --}}
                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                    <a href="{{ $ctaHref }}"
                        class="w-full inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl text-sm transition shadow">
                        {{ $ctaLabel }}
                    </a>
                    <div class="text-xs text-gray-600 font-semibold mt-2">Akses Cepat</div>
                    <a href="#" class="block text-sm text-blue-600 hover:underline">Gabung Komunitas Telegram</a>
                    <a href="#" class="block text-sm text-blue-600 hover:underline">Lihat Dokumen Pembelajaran</a>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
