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
*/

$ytId = $lesson->youtube_video_id ?? null;
$gdriveId = $lesson->gdrive_file_id ?? null;

$youtubeWatchUrl = $ytId ? "https://www.youtube.com/watch?v={$ytId}" : null;
$youtubeThumb = $ytId ? "https://i.ytimg.com/vi/{$ytId}/hqdefault.jpg" : null;

$youtubeEmbedAllowed = false;
$youtubeEmbedSrc = null;

if ($ytId) {
$youtubeEmbedSrc = 'https://www.youtube.com/embed/' . $ytId . '?rel=0';
if (!empty($lesson->start_time_seconds)) {
$youtubeEmbedSrc .= '&start=' . ((int) $lesson->start_time_seconds);
}

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

/** Flatten modul untuk prev/next */
$flat = [];
foreach(($modules ?? []) as $m){
foreach(($m['lessons'] ?? []) as $ls){
$flat[] = $ls;
}
}
$currIndex = collect($flat)->search(fn($l) => ($l['id'] ?? null) === ($lesson->id ?? null));
$prev = $currIndex !== false && $currIndex > 0 ? $flat[$currIndex-1] : null;
$next = $currIndex !== false && $currIndex < count($flat)-1 ? $flat[$currIndex+1] : null; @endphp <div
    class="bg-soft min-h-screen">
    <div class="max-w-[1500px] mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">

        {{-- Top bar --}}
        @include('user.lessons.partials.topbar', [
        'lesson' => $lesson,
        'course' => $course,
        ])

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
            @include('user.lessons.partials.sidebar-desktop', [
            'modules' => $modules,
            'lesson' => $lesson,
            'course' => $course,
            ])

            {{-- Main content --}}
            <main class="space-y-4 sm:space-y-5 lg:space-y-6">
                @include('user.lessons.partials.content', [
                'lesson' => $lesson,
                'ytId' => $ytId,
                'gdriveId' => $gdriveId,
                'youtubeEmbedAllowed' => $youtubeEmbedAllowed,
                'youtubeEmbedSrc' => $youtubeEmbedSrc,
                'youtubeWatchUrl' => $youtubeWatchUrl,
                'youtubeThumb' => $youtubeThumb,
                'gdriveEmbedSrc' => $gdriveEmbedSrc,
                ])

                @include('user.lessons.partials.prev-next', [
                'prev' => $prev,
                'next' => $next,
                ])
            </main>
        </div>

        {{-- Offcanvas mobile/tablet sidebar (di luar grid) --}}
        @include('user.lessons.partials.sidebar-offcanvas', [
        'modules' => $modules,
        'lesson' => $lesson,
        ])

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

    // Heuristic fallback iframe
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