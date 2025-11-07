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
</style>
@endpush

@section('content')
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

        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ route('user.courses.show', $course->id ?? ($lesson->course_id ?? '')) }}"
                class="hidden sm:inline-flex items-center px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-gray-100 hover:bg-gray-200 text-gray-800">
                Kembali ke Kursus
            </a>
            <button id="btn-open-sidebar"
                class="inline-flex sm:hidden items-center px-3 py-2 rounded-xl text-xs font-semibold bg-primary-accent text-white hover:brightness-95">
                Materi
            </button>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav class="text-[11px] sm:text-sm text-gray-500 mb-4">
        <a href="{{ route('user.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-1 sm:mx-2">/</span>
        <a href="{{ route('user.courses.show', $course->id ?? ($lesson->course_id ?? '')) }}" class="hover:underline">
            {{ $course->title ?? 'Detail Kursus' }}
        </a>
        <span class="mx-1 sm:mx-2">/</span>
        <span class="font-semibold text-gray-700">{{ $lesson->title ?? 'Pelajaran' }}</span>
    </nav>

    {{-- Grid: sidebar (desktop) + konten --}}
    <div class="grid grid-cols-1 lg:grid-cols-[22rem,1fr] gap-4 sm:gap-5">

        {{-- DESKTOP SIDEBAR (muncul ≥1024px) --}}
        <aside class="hidden lg:block">
            <div class="sticky top-4">
                <div
                    class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-4 max-h-[calc(100vh-5rem)] overflow-y-auto">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800">Materi</h2>
                        <a href="{{ route('user.courses.show', $course->id ?? ($lesson->course_id ?? '')) }}"
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
                                $type = $ls['type'] ?? 'video';
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

        {{-- OFFCANVAS (mobile & tablet) --}}
        <div id="offcanvas-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden"></div>
        <aside id="offcanvas"
            class="fixed z-50 inset-y-0 left-0 w-[85%] max-w-[22rem] bg-white shadow-xl border-r border-gray-100 p-4 offcanvas-enter hidden">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">Materi</h2>
                <button id="btn-close-sidebar" class="text-gray-600 hover:text-gray-800 px-2 py-1 rounded">
                    Tutup
                </button>
            </div>
            <div class="mt-4 h-[calc(100vh-5.5rem)] overflow-y-auto">
                @forelse(($modules ?? []) as $mod)
                <div class="rounded-xl border border-gray-100 overflow-hidden mb-3">
                    <div class="bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700">
                        {{ $mod['title'] ?? 'Modul' }}
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach(($mod['lessons'] ?? []) as $ls)
                        @php
                        $isActive = ($lesson->id ?? null) === ($ls['id'] ?? null);
                        $isDone = $ls['is_done'] ?? false;
                        $type = $ls['type'] ?? 'video';
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
            <a href="{{ route('user.courses.show', $course->id ?? ($lesson->course_id ?? '')) }}"
                class="mt-3 inline-flex items-center justify-center w-full px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200 text-gray-800">
                Kembali ke Kursus
            </a>
        </aside>

        {{-- KONTEN --}}
        <main class="space-y-5">
            @php
            $c = $content ?? [];
            $type = $c['type'] ?? ($lesson->type ?? 'video');
            @endphp

            @if($type === 'video')
            <article class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 overflow-hidden">
                <div class="bg-black/80 aspect-video">
                    <video class="w-full h-full" controls preload="metadata" @if(!empty($c['video']['poster']))
                        poster="{{ $c['video']['poster'] }}" @endif>
                        @if(!empty($c['video']['src']))
                        <source src="{{ $c['video']['src'] }}" type="video/mp4">
                        @endif
                        Browser Anda tidak mendukung video HTML5.
                    </video>
                </div>
                <div class="p-5">
                    @if(!empty($c['body']))
                    <p class="text-gray-700 leading-relaxed">{{ $c['body'] }}</p>
                    @endif
                    <div class="mt-4 flex gap-2">
                        <a href="#"
                            class="px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm font-semibold text-gray-800">Resource</a>
                        <a href="#"
                            class="px-3 py-2 rounded-xl bg-primary-accent hover:brightness-95 text-sm font-semibold text-white">Unduh
                            Materi</a>
                    </div>
                </div>
            </article>
            @elseif($type === 'text')
            <article class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-6">
                <div class="prose prose-slate max-w-none">
                    @if(!empty($c['body']['lead']))
                    <p><strong>Ringkasan:</strong> {{ $c['body']['lead'] }}</p>
                    @endif
                    @if(!empty($c['body']['html'])) {!! $c['body']['html'] !!} @endif
                    @if(!empty($c['body']['code']))
                    <pre
                        class="overflow-auto p-4 rounded-xl bg-gray-50 text-sm"><code>{{ $c['body']['code'] }}</code></pre>
                    @endif
                    @if(!empty($c['body']['tips']))
                    <div class="p-4 rounded-xl bg-green-50 border border-green-200">
                        <p class="m-0 text-sm"><strong>Tips:</strong> {{ $c['body']['tips'] }}</p>
                    </div>
                    @endif
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="#"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold">Resource</a>
                    <form action="#" method="post">@csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-secondary-highlight hover:brightness-95 text-white font-semibold">
                            Tandai Selesai
                        </button>
                    </form>
                </div>
            </article>
            @elseif($type === 'quiz')
            <article class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-6">
                <header class="mb-2">
                    <h2 class="text-xl font-extrabold text-gray-900">Kuis</h2>
                    @if(!empty($lesson->meta))
                    <p class="text-sm text-gray-500">{{ $lesson->meta }}</p>
                    @endif
                </header>

                <form action="#" method="post" class="space-y-6">@csrf
                    @forelse(($c['questions'] ?? []) as $idx => $q)
                    <fieldset class="border border-gray-100 rounded-xl p-4">
                        <legend class="px-2 text-sm font-semibold text-gray-700">{{ $idx + 1 }}) {{ $q['q'] ?? '' }}
                        </legend>
                        <div class="mt-3 space-y-2">
                            @foreach(($q['choices'] ?? []) as $cidx => $choice)
                            @php $inputId = "q{$idx}c{$cidx}"; @endphp
                            <div>
                                <input id="{{ $inputId }}" name="q{{ $idx }}" type="radio" class="peer hidden" />
                                <label for="{{ $inputId }}"
                                    class="flex items-start gap-2 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                    <span>{{ $choice['label'] ?? '' }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </fieldset>
                    @empty
                    <p class="text-gray-500">Belum ada pertanyaan.</p>
                    @endforelse

                    <div class="flex flex-wrap gap-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-accent hover:brightness-95 text-white font-semibold">
                            Kumpulkan Jawaban
                        </button>
                        <a href="{{ route('user.lessons.show', $lesson->id) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold">
                            Reset Pilihan
                        </a>
                    </div>
                </form>
            </article>
            @else
            <div class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-6">
                <p class="text-gray-600">Tipe konten tidak dikenali.</p>
            </div>
            @endif

            {{-- Prev / Next --}}
            @php
            $flat = [];
            foreach(($modules ?? []) as $m){ foreach(($m['lessons'] ?? []) as $ls){ $flat[] = $ls; } }
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
</script>
@endpush
