@extends('layouts.builder')

@section('title', ($lesson->title ?? 'Ruang Belajar'))

@push('styles')
<style>
    .prose :where(pre, code) {
        white-space: pre-wrap;
    }

    /* Layout utama untuk konten + sidebar desktop */
    .lesson-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        /* default: 1 kolom (mobile & tablet) */
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .lesson-layout {
            gap: 1.25rem;
        }
    }

    @media (min-width: 1024px) {
        .lesson-layout {
            grid-template-columns: 22rem minmax(0, 1fr);
            /* sidebar + konten */
        }

        /* Saat sidebar-collapsed, jadikan 1 kolom & sembunyikan sidebar dari layout */
        .lesson-layout.sidebar-collapsed {
            grid-template-columns: minmax(0, 1fr);
            /* cuma konten */
        }

        /* PAKSA sidebar hilang dari grid, override lg:block Tailwind */
        .lesson-layout.sidebar-collapsed #desktop-sidebar {
            display: none !important;
        }

        .desktop-sidebar {
            transition: opacity .2s ease;
        }
    }

    /* Offcanvas mobile */
    .offcanvas-enter {
        transform: translateX(-100%);
        opacity: 0;
    }

    .offcanvas-enter-active {
        transform: translateX(0);
        opacity: 1;
        transition: transform .2s ease, opacity .2s ease;
    }

    .offcanvas-exit {
        transform: translateX(0);
        opacity: 1;
    }

    .offcanvas-exit-active {
        transform: translateX(-100%);
        opacity: 0;
        transition: transform .2s ease, opacity .2s ease;
    }

    /* Fallback overlay styling */
    .yt-fallback {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
        text-align: center;
        padding: 1rem;
    }

    .yt-fallback img {
        max-width: 240px;
        border-radius: 8px;
        display: block;
        margin: 0 auto 0.75rem;
    }
</style>
@endpush

@section('content')
@php
/**
* SERVER-SIDE PREPARATION
* - Buat src URL secara aman (hindari directive Blade di atribut)
* - Periksa YouTube oEmbed (200 => kemungkinan embed diperbolehkan)
* - Siapkan fallback thumbnail & watch URL
*
* Catatan: pemanggilan curl ini akan berjalan saat view dirender.
* Untuk produksi, pertimbangkan caching hasil oEmbed per video ID.
*/

$ytId = $lesson->youtube_video_id ?? null;
$gdriveId = $lesson->gdrive_file_id ?? null;

$youtubeWatchUrl = $ytId ? "https://www.youtube.com/watch?v={$ytId}" : null;
$youtubeThumb = $ytId ? "https://i.ytimg.com/vi/{$ytId}/hqdefault.jpg" : null;

$youtubeEmbedAllowed = false;
$youtubeEmbedSrc = null;

if ($ytId) {
// Build embed src with optional start time
$youtubeEmbedSrc = 'https://www.youtube.com/embed/' . $ytId . '?rel=0';
if (!empty($lesson->start_time_seconds)) {
$youtubeEmbedSrc .= '&start=' . ((int) $lesson->start_time_seconds);
}

// oEmbed check (simple curl)
$oembedUrl = 'https://www.youtube.com/oembed?url=' . urlencode($youtubeWatchUrl) . '&format=json';
$ch = curl_init($oembedUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 4);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$youtubeEmbedAllowed = ($httpCode === 200);
}

$gdriveEmbedSrc = $gdriveId ? 'https://drive.google.com/file/d/' . $gdriveId . '/preview' : null;
@endphp

<div class="bg-soft min-h-screen">
    <div class="max-w-[1500px] mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">

        {{-- Top bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4 sm:mb-6">
            <div class="min-w-0">
                <p class="text-[11px] sm:text-xs font-semibold tracking-[0.18em] text-gray-500 uppercase">
                    Pelajaran
                </p>
                <h1
                    class="mt-1 text-[1.35rem] sm:text-2xl lg:text-[1.8rem] leading-snug font-extrabold text-dark line-clamp-2">
                    {{ $lesson->title ?? 'Pelajaran' }}
                </h1>
                @if(!empty($lesson->meta))
                <p class="mt-1 text-xs sm:text-sm text-gray-500">{{ $lesson->meta }}</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                {{-- Toggle sidebar desktop --}}
                <button id="btn-toggle-desktop-sidebar"
                    class="hidden lg:inline-flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50 text-gray-800 text-sm font-medium">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h10M4 18h8" />
                    </svg>
                    <span id="btn-toggle-desktop-sidebar-text">Sembunyikan Materi</span>
                </button>

                {{-- Tombol offcanvas mobile/tablet --}}
                <button id="btn-open-sidebar"
                    class="inline-flex lg:hidden items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50 text-gray-800 text-sm font-medium">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>Materi</span>
                </button>

                <a href="{{ route('user.courses.show', $course->id) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-brand text-white text-sm font-semibold hover:brightness-95 shadow-custom-soft">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline">Kembali ke Kursus</span>
                    <span class="sm:hidden">Kursus</span>
                </a>
            </div>
        </div>

        {{-- Breadcrumb --}}
        <nav class="mb-4 sm:mb-6 text-[11px] sm:text-[13px] text-gray-500">
            <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto no-scrollbar">
                <a href="{{ route('user.dashboard') }}" class="whitespace-nowrap hover:text-brand hover:underline">
                    Dashboard
                </a>
                <span>/</span>
                <a href="{{ route('user.courses.show', $course->id) }}"
                    class="whitespace-nowrap hover:text-brand hover:underline">
                    {{ $course->title ?? 'Detail Kursus' }}
                </a>
                <span>/</span>
                <span class="whitespace-nowrap font-semibold text-gray-700">
                    {{ $lesson->title ?? 'Pelajaran' }}
                </span>
            </div>
        </nav>

        <div id="lesson-layout" class="lesson-layout">

            {{-- Desktop sidebar --}}
            <aside id="desktop-sidebar" class="hidden lg:block desktop-sidebar">
                <div class="sticky top-24">
                    <div
                        class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-4 lg:p-5 max-h-[calc(100vh-7rem)] overflow-y-auto">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-base lg:text-lg font-bold text-gray-800">Materi Kursus</h2>
                            <a href="{{ route('user.courses.show', $course->id) }}"
                                class="text-[11px] text-brand hover:underline">
                                Lihat Semua
                            </a>
                        </div>
                        <div class="mt-4 space-y-4">
                            @forelse(($modules ?? []) as $mod)
                            <div class="rounded-2xl border border-gray-100 overflow-hidden bg-soft/40">
                                <div
                                    class="bg-gray-50 px-3.5 py-2.5 text-[13px] font-semibold text-gray-700 flex items-center justify-between">
                                    <span class="line-clamp-1">
                                        {{ $mod['title'] ?? 'Modul' }}
                                    </span>
                                </div>
                                <ul class="divide-y divide-gray-100">
                                    @foreach(($mod['lessons'] ?? []) as $ls)
                                    @php
                                    $isActive = ($lesson->id ?? null) === ($ls['id'] ?? null);
                                    $isDone = $ls['is_done'] ?? false;
                                    $type = $ls['kind'] ?? 'youtube';
                                    @endphp
                                    <li>
                                        <a href="{{ route('user.lessons.show', $ls['id']) }}"
                                            class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] {{ $isActive ? 'bg-brand/8 text-brand font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                            @if($type === 'quiz')
                                            <span title="Quiz"
                                                class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">?</span>
                                            @else
                                            <span
                                                class="inline-flex items-center justify-center w-6 h-6 text-[11px] rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">▶</span>
                                            @endif
                                            <span class="flex-1 line-clamp-2">{{ $ls['title'] ?? 'Pelajaran' }}</span>
                                            @if($isDone)
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            @endif
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @empty
                            <div class="text-sm text-gray-500">Belum ada modul.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main content --}}
            <main class="space-y-4 sm:space-y-5 lg:space-y-6">

                {{-- Video / Text / Quiz handling --}}
                @if($lesson->kind === 'youtube' || $lesson->kind === 'gdrive')
                <article class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 overflow-hidden">
                    <div class="relative bg-black/80 aspect-video">
                        {{-- YouTube preferensi pertama (jika ada) --}}
                        @if($ytId)
                        @if($youtubeEmbedAllowed && !empty($youtubeEmbedSrc))
                        <iframe id="lesson-iframe" class="w-full h-full" src="{{ $youtubeEmbedSrc }}"
                            title="YouTube video player" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                        @else
                        {{-- Tampilkan fallback langsung (embed diblokir atau oEmbed gagal) --}}
                        <a href="{{ $youtubeWatchUrl }}" target="_blank" rel="noopener noreferrer"
                            class="yt-fallback inline-flex flex-col items-center justify-center">
                            <img src="{{ $youtubeThumb }}" alt="Thumbnail">
                            <div
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-brand text-white text-sm font-semibold shadow">
                                Buka di YouTube
                            </div>
                            <p class="mt-2 text-xs sm:text-sm text-white/90">Embedding terblokir — buka di YouTube</p>
                        </a>
                        @endif

                        {{-- Kalau tidak ada YouTube tapi ada Google Drive --}}
                        @elseif($gdriveId)
                        {{-- Google Drive preview --}}
                        <iframe id="lesson-iframe" class="w-full h-full" src="{{ $gdriveEmbedSrc }}"
                            allow="autoplay"></iframe>

                        @else
                        <div
                            class="w-full h-full flex items-center justify-center text-gray-300 text-sm sm:text-base px-4">
                            Video tidak tersedia.
                        </div>
                        @endif
                    </div>

                    <div class="p-4 sm:p-5 lg:p-6 space-y-3 sm:space-y-4">
                        @if(!empty($lesson->description))
                        <p class="text-[13px] sm:text-sm lg:text-[15px] text-gray-700 leading-relaxed">
                            {{ $lesson->description }}
                        </p>
                        @endif

                        <div class="flex flex-wrap gap-2 mt-1">
                            {{-- tempat resource tambahan --}}
                        </div>
                    </div>
                </article>

                @elseif($lesson->kind === 'text')
                <article class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-4 sm:p-5 lg:p-6">
                    <div class="prose prose-slate max-w-none prose-sm sm:prose-base">
                        @if(!empty($lesson->description))
                        <p class="lead text-sm sm:text-base mb-3">
                            <strong>Ringkasan:</strong> {{ $lesson->description }}
                        </p>
                        @endif

                        @if(!empty($lesson->content))
                        {!! $lesson->content !!}
                        @else
                        <p>Konten pelajaran ini belum tersedia.</p>
                        @endif
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <form action="#" method="post">@csrf
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-accent hover:brightness-95 text-white text-sm font-semibold">
                                Tandai Selesai
                            </button>
                        </form>
                    </div>
                </article>

                @elseif($lesson->kind === 'quiz')
                <article class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-4 sm:p-5 lg:p-6">
                    <header class="mb-3 sm:mb-4">
                        <h2 class="text-lg sm:text-xl font-extrabold text-dark">Kuis</h2>
                        @if(!empty($lesson->meta))
                        <p class="mt-1 text-xs sm:text-sm text-gray-500">{{ $lesson->meta }}</p>
                        @endif
                    </header>

                    <form action="#" method="post" class="space-y-4 sm:space-y-5">@csrf
                        @if($lesson->quiz)
                        @forelse($lesson->quiz->questions as $idx => $question)
                        <fieldset class="border border-gray-100 rounded-2xl p-3.5 sm:p-4 bg-soft/40">
                            <legend class="px-1.5 text-sm font-semibold text-gray-800">
                                {{ $idx + 1 }}) {{ $question->question_text ?? 'Pertanyaan' }}
                            </legend>
                            <div class="mt-3 space-y-2.5">
                                @foreach($question->choices as $cidx => $choice)
                                @php $inputId = "q{$idx}c{$cidx}"; @endphp
                                <div>
                                    <input id="{{ $inputId }}" name="q{{ $idx }}" value="{{ $choice->id }}" type="radio"
                                        class="peer hidden" />
                                    <label for="{{ $inputId }}"
                                        class="flex items-start gap-2 p-3 rounded-2xl border border-gray-200 cursor-pointer hover:bg-white peer-checked:border-brand peer-checked:bg-brand/5 peer-checked:ring-1 peer-checked:ring-brand/60 text-sm">
                                        <span>{{ $choice->text ?? 'Pilihan' }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </fieldset>
                        @empty
                        <p class="text-sm text-gray-500">Belum ada pertanyaan untuk kuis ini.</p>
                        @endforelse
                        @else
                        <p class="text-sm text-gray-500">Data kuis tidak ditemukan.</p>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-brand hover:brightness-95 text-white text-sm font-semibold">
                                Kumpulkan Jawaban
                            </button>
                        </div>
                    </form>
                </article>

                @else
                <div class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-4 sm:5">
                    <p class="text-sm sm:text-base text-gray-600">Tipe konten ({{ $lesson->kind }}) tidak dikenali.</p>
                </div>
                @endif

                {{-- Prev / Next --}}
                @php
                $flat = [];
                foreach(($modules ?? []) as $m){
                foreach(($m['lessons'] ?? []) as $ls){
                $flat[] = $ls;
                }
                }
                $currIndex = collect($flat)->search(fn($l) => ($l['id'] ?? null) === ($lesson->id ?? null));
                $prev = $currIndex !== false && $currIndex > 0 ? $flat[$currIndex-1] : null;
                $next = $currIndex !== false && $currIndex < count($flat)-1 ? $flat[$currIndex+1] : null; @endphp <div
                    class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 px-3.5 py-3 sm:px-4 sm:py-3.5 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 justify-between">
                    @if($prev)
                    <a href="{{ route('user.lessons.show', $prev['id']) }}"
                        class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs sm:text-sm font-semibold">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span class="line-clamp-1">{{ $prev['title'] ?? 'Sebelumnya' }}</span>
                    </a>
                    @else
                    <span class="text-[11px] text-gray-400">Awal modul</span>
                    @endif

                    @if($next)
                    <a href="{{ route('user.lessons.show', $next['id']) }}"
                        class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-brand hover:brightness-95 text-white text-xs sm:text-sm font-semibold">
                        <span class="line-clamp-1">{{ $next['title'] ?? 'Berikutnya' }}</span>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    @else
                    <span class="text-[11px] text-gray-400 text-right">Tidak ada pelajaran berikutnya</span>
                    @endif
        </div>

        </main>
    </div>

    {{-- Offcanvas mobile/tablet sidebar (di luar grid, biar tidak ngaruh layout) --}}
    <div id="offcanvas-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden"></div>
    <aside id="offcanvas"
        class="fixed z-50 inset-y-0 left-0 w-[88%] max-w-[22rem] bg-white shadow-xl border-r border-gray-100 p-4 sm:p-5 offcanvas-enter hidden">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="text-base sm:text-lg font-bold text-gray-800">Materi Kursus</h3>
            <button id="btn-close-sidebar"
                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="space-y-4 overflow-y-auto max-h-[calc(100vh-6rem)] pr-1">
            @forelse(($modules ?? []) as $mod)
            <div class="rounded-2xl border border-gray-100 overflow-hidden bg-soft/40">
                <div class="bg-gray-50 px-3.5 py-2.5 text-[13px] font-semibold text-gray-700">
                    {{ $mod['title'] ?? 'Modul' }}
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach(($mod['lessons'] ?? []) as $ls)
                    @php
                    $isActive = ($lesson->id ?? null) === ($ls['id'] ?? null);
                    $isDone = $ls['is_done'] ?? false;
                    $type = $ls['kind'] ?? 'youtube';
                    @endphp
                    <li>
                        <a href="{{ route('user.lessons.show', $ls['id']) }}"
                            class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] {{ $isActive ? 'bg-brand/8 text-brand font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                            @if($type === 'quiz')
                            <span title="Quiz"
                                class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">?</span>
                            @else
                            <span
                                class="inline-flex items-center justify-center w-6 h-6 text-[11px] rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">▶</span>
                            @endif
                            <span class="flex-1 line-clamp-2">{{ $ls['title'] ?? 'Pelajaran' }}</span>
                            @if($isDone)
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            @endif
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @empty
            <div class="text-sm text-gray-500">Belum ada modul.</div>
            @endforelse
        </div>
    </aside>

</div>
</div>
@endsection

@push('scripts')
<script>
    // Sidebar offcanvas controls (mobile & tablet)
    const oc  = document.getElementById('offcanvas');
    const bg  = document.getElementById('offcanvas-backdrop');
    const btnOpen  = document.getElementById('btn-open-sidebar');
    const btnClose = document.getElementById('btn-close-sidebar');

    function openSidebar(){
        if(!oc || !bg) return;
        oc.classList.remove('hidden','offcanvas-exit','offcanvas-exit-active');
        bg.classList.remove('hidden');
        requestAnimationFrame(()=>{
            oc.classList.add('offcanvas-enter-active');
            bg.style.opacity = '1';
        });
    }
    function closeSidebar(){
        if(!oc || !bg) return;
        oc.classList.remove('offcanvas-enter-active');
        oc.classList.add('offcanvas-exit-active');
        bg.style.opacity = '0';
        setTimeout(()=>{
            oc.classList.add('hidden');
            oc.classList.remove('offcanvas-exit-active');
            bg.classList.add('hidden');
        }, 180);
    }

    btnOpen && btnOpen.addEventListener('click', openSidebar);
    btnClose && btnClose.addEventListener('click', closeSidebar);
    bg && bg.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeSidebar(); });

    // Toggle sidebar desktop (hide/show)
    (function () {
        const layout = document.getElementById('lesson-layout');
        const btnToggleDesktop = document.getElementById('btn-toggle-desktop-sidebar');
        const btnText = document.getElementById('btn-toggle-desktop-sidebar-text');

        if (!layout || !btnToggleDesktop || !btnText) return;

        btnToggleDesktop.addEventListener('click', () => {
            const collapsed = layout.classList.toggle('sidebar-collapsed');

            if (collapsed) {
                btnText.textContent = 'Tampilkan Materi';
            } else {
                btnText.textContent = 'Sembunyikan Materi';
            }
        });
    })();

    // Heuristic fallback: jika iframe gagal load (embedding diblokir by CSP/age/region),
    // kita tampilkan fallback setelah timeout singkat.
    (function(){
        const iframe = document.getElementById('lesson-iframe');
        if(!iframe) return;
        const container = iframe.closest('.relative') || iframe.parentElement;
        const fallback = container ? container.querySelector('.yt-fallback') : null;
        if(!fallback) return;

        const t = setTimeout(()=> {
            fallback.style.display = 'flex';
            iframe.style.display = 'none';
        }, 3000);

        iframe.addEventListener('load', ()=> {
            clearTimeout(t);
        });
    })();
</script>
@endpush