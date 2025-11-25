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
                'currentProgress' => $currentProgress,
                ])

                @include('user.lessons.partials.prev-next', [
                'prev' => $prev,
                'next' => $next,
                'isCompleted' => $currentProgress && $currentProgress->status === 'completed',
                'course' => $course,
                'enrollment' => $enrollment ?? null,
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

    // --- Lesson Progress & Completion Logic ---
    document.addEventListener('DOMContentLoaded', function() {
        const lessonId = "{{ $lesson->id }}";
        const lessonDuration = {{ $lesson->duration_seconds ?? 0 }};
        const isVideo = {{ ($lesson->kind === 'video' || $lesson->youtube_video_id) ? 'true' : 'false' }};
        const isYoutube = {{ $lesson->youtube_video_id ? 'true' : 'false' }};
        
        // Completion status
        let isCompleted = {{ $currentProgress && $currentProgress->status === 'completed' ? 'true' : 'false' }};
        
        // Initial values from server
        let serverSpent = {{ $currentProgress->duration_seconds ?? 0 }};
        let lastWatched = {{ $currentProgress->last_watched_second ?? 0 }};
        
        let sessionSpent = 0; // Seconds spent in this session
        let lastSyncedSessionSpent = 0; // Track what we've already sent
        let currentVideoTime = lastWatched; // For video tracking
        let isVideoPlaying = false; // Track video state
        
        const completionArea = document.getElementById('completion-area') || document.getElementById('completion-area-text');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const timeCounterEl = document.getElementById('time-counter') || document.getElementById('time-counter-text');

        // Helper: Format seconds to MM:SS
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }

        // Update time display (countdown)
        function updateTimeDisplay() {
            if (timeCounterEl) {
                const unsynced = sessionSpent - lastSyncedSessionSpent;
                const totalSpent = serverSpent + unsynced;
                const remaining = Math.max(0, lessonDuration - totalSpent);
                timeCounterEl.textContent = formatTime(remaining);
            }
        }

        // Initialize display
        updateTimeDisplay();

        // 1. Timer for Time Spent (every 1s)
        // Always count page time, regardless of video play state
        setInterval(() => {
            sessionSpent++;
            updateTimeDisplay();
            checkCompletion();
            
            // Auto-submit when timer reaches zero
            const unsynced = sessionSpent - lastSyncedSessionSpent;
            const totalSpent = serverSpent + unsynced;
            const remaining = Math.max(0, lessonDuration - totalSpent);
            
            if (remaining === 0 && !document.querySelector('.completion-success')) {
                // Timer has ended, auto-submit
                submitCompletion();
            }
        }, 1000);

        // 2. Sync to Server (every 10s)
        setInterval(() => {
            syncProgress();
        }, 10000);

        // 3. Check Completion Visibility
        function checkCompletion() {
            if (!completionArea) return;
            if (completionArea.classList.contains('hidden') === false) return; // Already shown

            const unsynced = sessionSpent - lastSyncedSessionSpent;
            const totalSpent = serverSpent + unsynced;
            
            let show = false;

            if (isVideo) {
                // Video: Time Spent >= Duration - 30s OR Video Position >= Duration - 30s
                const threshold = Math.max(0, lessonDuration - 30);
                if (totalSpent >= threshold || currentVideoTime >= threshold) {
                    show = true;
                }
            } else {
                // GDrive/Text: Time Spent >= Duration - 60s
                const threshold = Math.max(0, lessonDuration - 60);
                if (totalSpent >= threshold) {
                    show = true;
                }
            }

            if (show) {
                completionArea.classList.remove('hidden');
            }
        }

        // 5. Submit Completion (AJAX)
        function submitCompletion() {
            // Prevent multiple submissions
            if (window.submittingCompletion) return;
            window.submittingCompletion = true;

            fetch("{{ route('user.lessons.complete', $lesson->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({})
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Hide completion button, show success message
                    if (completionArea) {
                        completionArea.innerHTML = `
                            <div class="flex items-center gap-2 text-sm text-emerald-600 font-semibold completion-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle-2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                <span>Pelajaran Telah Selesai</span>
                            </div>
                        `;
                    }
                    
                    // Enable next lesson navigation
                    enableNextLesson();
                    
                    // Mark as completed locally to stop sync
                    isCompleted = true;
                }
            })
            .catch(err => {
                console.error('Completion failed', err);
                window.submittingCompletion = false;
            });
        }

        // 6. Enable Next Lesson Navigation
        function enableNextLesson() {
            // 1. Update current lesson checkmark in sidebars
            const currentLessonLinks = document.querySelectorAll(`a[href*="${lessonId}"]`);
            currentLessonLinks.forEach(link => {
                // Add checkmark if not already present
                if (!link.querySelector('svg[class*="emerald"]')) {
                    const checkmark = document.createElement('svg');
                    checkmark.className = 'w-4 h-4 text-emerald-600 shrink-0';
                    checkmark.setAttribute('fill', 'none');
                    checkmark.setAttribute('stroke', 'currentColor');
                    checkmark.setAttribute('viewBox', '0 0 24 24');
                    checkmark.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />';
                    link.appendChild(checkmark);
                }
            });

            // 2. Find the next lesson ID
            const allLessonLinks = Array.from(document.querySelectorAll('a[href*="/lessons/"]'));
            const currentIndex = allLessonLinks.findIndex(link => link.href.includes(lessonId));
            let nextLessonHref = null;
            
            if (currentIndex !== -1 && currentIndex < allLessonLinks.length - 1) {
                nextLessonHref = allLessonLinks[currentIndex + 1].href;
            }

            // 3. Enable next lesson in prev-next navigation
            const disabledNextDiv = document.querySelector('div[class*="bg-gray-300"][class*="cursor-not-allowed"]');
            if (disabledNextDiv && nextLessonHref) {
                const nextTitle = disabledNextDiv.querySelector('span')?.textContent;
                const nextSvg = disabledNextDiv.querySelector('svg')?.outerHTML;

                const newLink = document.createElement('a');
                newLink.href = nextLessonHref;
                newLink.className = 'inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-brand hover:brightness-95 text-white text-xs sm:text-sm font-semibold';
                newLink.innerHTML = `<span class="line-clamp-1">${nextTitle}</span>${nextSvg}`;
                disabledNextDiv.replaceWith(newLink);
            }

            // 4. Enable next lesson in sidebars (both desktop and offcanvas)
            const disabledLessons = document.querySelectorAll('div[data-lesson-id][class*="cursor-not-allowed"]');
            
            disabledLessons.forEach(disabledDiv => {
                const nextLessonId = disabledDiv.getAttribute('data-lesson-id');
                
                // Check if this is the immediate next lesson
                if (nextLessonHref && nextLessonHref.includes(nextLessonId)) {
                    const lessonTitle = disabledDiv.querySelector('span.line-clamp-2')?.textContent;
                    const isQuiz = disabledDiv.querySelector('span[title="Quiz"]');
                    
                    // Create new active link
                    const newLink = document.createElement('a');
                    newLink.href = nextLessonHref;
                    newLink.className = 'flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-gray-700 hover:bg-gray-50';
                    
                    const iconClass = 'inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border border-gray-300 text-gray-400 bg-white';
                    const iconContent = isQuiz ? '?' : '▶';
                    
                    newLink.innerHTML = `
                        <span class="${iconClass}">${iconContent}</span>
                        <span class="flex-1 line-clamp-2">${lessonTitle}</span>
                    `;
                    
                    disabledDiv.replaceWith(newLink);
                }
            });
        }

        // Attach AJAX handler to completion buttons
        const completionButtons = document.querySelectorAll('#btn-mark-complete, #btn-mark-complete-text');
        completionButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                submitCompletion();
            });
        });

        // 4. Sync Function
        function syncProgress() {
            if (isCompleted) return; // Stop syncing if already completed

            const delta = sessionSpent - lastSyncedSessionSpent;
            if (delta <= 0 && !isYoutube) return; // Nothing to sync if not video (video needs position update)

            const payload = {
                add_seconds: delta,
                _token: csrfToken
            };

            if (isYoutube) {
                payload.last_watched_second = Math.floor(currentVideoTime);
            }

            fetch("{{ route('user.lessons.progress', $lesson->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update local baseline
                    serverSpent = data.progress.duration_seconds;
                    lastSyncedSessionSpent = sessionSpent; // Mark as synced
                }
            })
            .catch(err => console.error('Progress sync failed', err));
        }

        // 5. YouTube API Integration
        if (isYoutube) {
            // Load API if not already loaded
            if (!window.YT) {
                const tag = document.createElement('script');
                tag.src = "https://www.youtube.com/iframe_api";
                const firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }

            window.onYouTubeIframeAPIReady = function() {
                new YT.Player('lesson-iframe', {
                    events: {
                        'onReady': onPlayerReady,
                        'onStateChange': onPlayerStateChange
                    }
                });
            };

            function onPlayerReady(event) {
                // Seek to last watched
                if (lastWatched > 0) {
                    event.target.seekTo(lastWatched);
                }
                
                // Track current time every 1s
                setInterval(() => {
                    if (event.target && event.target.getCurrentTime) {
                        currentVideoTime = event.target.getCurrentTime();
                        checkCompletion(); // Check immediately if video position jumps
                    }
                }, 1000);
            }

            function onPlayerStateChange(event) {
                if (event.data === YT.PlayerState.PLAYING) {
                    isVideoPlaying = true;
                } else if (event.data === YT.PlayerState.PAUSED) {
                    isVideoPlaying = false;
                    syncProgress(); // Save position immediately on pause
                } else if (event.data === YT.PlayerState.ENDED) {
                    isVideoPlaying = false;
                    syncProgress(); // Save final position
                    checkCompletion();
                }
            }
        }
    });
    </script>
    @endpush