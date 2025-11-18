<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4 sm:mb-6">
    <div class="min-w-0">
        <p class="text-[11px] sm:text-xs font-semibold tracking-[0.18em] text-gray-500 uppercase">
            Pelajaran
        </p>
        <h1 class="mt-1 text-[1.35rem] sm:text-2xl lg:text-[1.8rem] leading-snug font-extrabold text-dark line-clamp-2">
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h8" />
            </svg>
            <span id="btn-toggle-desktop-sidebar-text">Sembunyikan Materi</span>
        </button>

        {{-- Tombol offcanvas mobile/tablet --}}
        <button id="btn-open-sidebar"
            class="inline-flex lg:hidden items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50 text-gray-800 text-sm font-medium">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
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