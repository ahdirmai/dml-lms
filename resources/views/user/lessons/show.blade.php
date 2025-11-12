@extends('layouts.builder')

@section('title', ($lesson->title ?? 'Ruang Belajar'))

@push('styles')
<style>
    .prose :where(pre, code) {
        white-space: pre-wrap;
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
// don't download body fully if not needed; still execute to get HTTP code
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$youtubeEmbedAllowed = ($httpCode === 200);
}

$gdriveEmbedSrc = $gdriveId ? 'https://drive.google.com/file/d/' . $gdriveId . '/preview' : null;
@endphp

<div class="max-w-[1500px] mx-auto px-3 sm:px-4 py-4 sm:py-6">

    {{-- Top bar --}}
    <div class="flex items-center justify-between mb-4 sm:mb-6">
        <div class="min-w-0">
            <p class="text-[11px] sm:text-xs font-bold tracking-wider text-gray-500">PELAJARAN</p>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-gray-900 truncate">
                {{ $lesson->title ?? 'Pelajaran' }}
            </h1>
            @if(!empty($lesson->meta))
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $lesson->meta }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <button id="btn-open-sidebar"
                class="lg:hidden inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="text-sm">Materi</span>
            </button>

            <a href="{{ route('user.courses.show', $course->id) }}"
                class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-primary-accent text-white font-semibold hover:brightness-95">
                Kembali ke Kursus
            </a>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav class="text-[11px] sm:text-sm text-gray-500 mb-4">
        <a href="{{ route('user.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-1 sm:mx-2">/</span>
        <a href="{{ route('user.courses.show', $course->id) }}" class="hover:underline">{{ $course->title ?? 'Detail
            Kursus' }}</a>
        <span class="mx-1 sm:mx-2">/</span>
        <span class="font-semibold text-gray-700">{{ $lesson->title ?? 'Pelajaran' }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-[22rem,1fr] gap-4 sm:gap-5">

        {{-- Desktop sidebar --}}
        <aside class="hidden lg:block">
            <div class="sticky top-4">
                <div
                    class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-4 max-h-[calc(100vh-5rem)] overflow-y-auto">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800">Materi</h2>
                        <a href="{{ route('user.courses.show', $course->id) }}"
                            class="text-xs text-primary-accent hover:underline">Kembali</a>
                    </div>
                    <div class="mt-4 space-y-4">
                        @forelse(($modules ?? []) as $mod)
                        <div class="rounded-xl border border-gray-100 overflow-hidden">
                            <div class="bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700">
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
                                        class="flex items-center gap-3 px-3 py-2 text-sm {{ $isActive ? 'bg-primary-accent/10 text-primary-accent font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                        @if($type === 'quiz')
                                        <span title="Quiz"
                                            class="inline-flex items-center justify-center w-5 h-5 rounded-full border {{ $isActive ? 'border-primary-accent text-primary-accent' : 'border-gray-300 text-gray-400' }}">?</span>
                                        @else
                                        <span
                                            class="inline-flex items-center justify-center w-5 h-5 rounded-full border {{ $isActive ? 'border-primary-accent text-primary-accent' : 'border-gray-300 text-gray-400' }}">▶</span>
                                        @endif
                                        <span class="flex-1">{{ $ls['title'] ?? 'Pelajaran' }}</span>
                                        @if($isDone)
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
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
                </div>
            </div>
        </aside>

        {{-- Offcanvas mobile --}}
        <div id="offcanvas-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden"></div>
        <aside id="offcanvas"
            class="fixed z-50 inset-y-0 left-0 w-[85%] max-w-[22rem] bg-white shadow-xl border-r border-gray-100 p-4 offcanvas-enter hidden">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-bold">Materi</h3>
                <button id="btn-close-sidebar"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-gray-100 hover:bg-gray-200">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4 overflow-y-auto max-h-[calc(100vh-5rem)]">
                @forelse(($modules ?? []) as $mod)
                <div class="rounded-xl border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700">
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
                                class="flex items-center gap-3 px-3 py-2 text-sm {{ $isActive ? 'bg-primary-accent/10 text-primary-accent font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                @if($type === 'quiz')
                                <span title="Quiz"
                                    class="inline-flex items-center justify-center w-5 h-5 rounded-full border {{ $isActive ? 'border-primary-accent text-primary-accent' : 'border-gray-300 text-gray-400' }}">?</span>
                                @else
                                <span
                                    class="inline-flex items-center justify-center w-5 h-5 rounded-full border {{ $isActive ? 'border-primary-accent text-primary-accent' : 'border-gray-300 text-gray-400' }}">▶</span>
                                @endif
                                <span class="flex-1">{{ $ls['title'] ?? 'Pelajaran' }}</span>
                                @if($isDone)
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
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

        {{-- Main content --}}
        <main class="space-y-5">

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
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-accent rounded-xl">Buka di
                            YouTube</div>
                        <p class="mt-2 text-sm text-white/90">Embedding terblokir — buka di YouTube</p>
                    </a>
                    @endif

                    {{-- Kalau tidak ada YouTube tapi ada Google Drive --}}
                    @elseif($gdriveId)
                    {{-- Google Drive preview --}}
                    <iframe id="lesson-iframe" class="w-full h-full" src="{{ $gdriveEmbedSrc }}"
                        allow="autoplay"></iframe>

                    @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        Video tidak tersedia.
                    </div>
                    @endif
                </div>

                <div class="p-5">
                    @if(!empty($lesson->description))
                    <p class="text-gray-700 leading-relaxed">{{ $lesson->description }}</p>
                    @endif
                    <div class="mt-4 flex gap-2">
                        {{-- space for resources --}}
                    </div>
                </div>
            </article>

            @elseif($lesson->kind === 'text')
            <article class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-6">
                <div class="prose prose-slate max-w-none">
                    @if(!empty($lesson->description))
                    <p class="lead"><strong>Ringkasan:</strong> {{ $lesson->description }}</p>
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
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-secondary-highlight hover:brightness-95 text-white font-semibold">
                            Tandai Selesai
                        </button>
                    </form>
                </div>
            </article>

            @elseif($lesson->kind === 'quiz')
            <article class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-6">
                <header class="mb-2">
                    <h2 class="text-xl font-extrabold text-gray-900">Kuis</h2>
                    @if(!empty($lesson->meta))
                    <p class="text-sm text-gray-500">{{ $lesson->meta }}</p>
                    @endif
                </header>

                <form action="#" method="post" class="space-y-6">@csrf
                    @if($lesson->quiz)
                    @forelse($lesson->quiz->questions as $idx => $question)
                    <fieldset class="border border-gray-100 rounded-xl p-4">
                        <legend class="px-2 text-sm font-semibold text-gray-700">{{ $idx + 1 }}) {{
                            $question->question_text ?? 'Pertanyaan' }}</legend>
                        <div class="mt-3 space-y-2">
                            @foreach($question->choices as $cidx => $choice)
                            @php $inputId = "q{$idx}c{$cidx}"; @endphp
                            <div>
                                <input id="{{ $inputId }}" name="q{{ $idx }}" value="{{ $choice->id }}" type="radio"
                                    class="peer hidden" />
                                <label for="{{ $inputId }}"
                                    class="flex items-start gap-2 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                    <span>{{ $choice->text ?? 'Pilihan' }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </fieldset>
                    @empty
                    <p class="text-gray-500">Belum ada pertanyaan untuk kuis ini.</p>
                    @endforelse
                    @else
                    <p class="text-gray-500">Data kuis tidak ditemukan.</p>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-accent hover:brightness-95 text-white font-semibold">
                            Kumpulkan Jawaban
                        </button>
                    </div>
                </form>
            </article>

            @else
            <div class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-6">
                <p class="text-gray-600">Tipe konten ({{ $lesson->kind }}) tidak dikenali.</p>
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
                class="flex flex-col sm:flex-row sm:items-center gap-3">
                @if($prev)
                <a href="{{ route('user.lessons.show', $prev['id']) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ $prev['title'] ?? 'Sebelumnya' }}
                </a>
                @endif

                @if($next)
                <a href="{{ route('user.lessons.show', $next['id']) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-accent hover:brightness-95 text-white font-semibold">
                    {{ $next['title'] ?? 'Berikutnya' }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                @endif
    </div>

    </main>
</div>
</div>
@endsection

@push('scripts')
<script>
    // Sidebar offcanvas controls
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

    // Heuristic fallback: jika iframe gagal load (embedding diblokir by CSP/age/region),
    // kita tampilkan fallback setelah timeout singkat. Ini bukan 100% akurat
    // karena cross-origin, tetapi memperbaiki UX ketika embed blocked.
    (function(){
        const iframe = document.getElementById('lesson-iframe');
        if(!iframe) return;
        // fallback element is the first .yt-fallback inside same container (if any)
        const container = iframe.closest('.relative') || iframe.parentElement;
        const fallback = container ? container.querySelector('.yt-fallback') : null;
        if(!fallback) return;

        // Wait for load event; if not fired quickly, show fallback
        const t = setTimeout(()=> {
            // Show fallback UI, hide iframe
            fallback.style.display = 'flex';
            iframe.style.display = 'none';
        }, 3000);

        iframe.addEventListener('load', ()=> {
            clearTimeout(t);
            // keep iframe visible
        });

        // If user clicks fallback, open in new tab (link already present in markup)
        // Nothing more to do here.
    })();
</script>
@endpush