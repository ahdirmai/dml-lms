{{-- resources/views/user/courses/partials/header.blade.php --}}
@props([
'course',
'ratingText' => null,
'author' => null,
'totalModules' => 0,
'totalLessons' => 0,
])

<section class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                @if($course->categories->isNotEmpty())
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-brand/10 text-brand">
                    {{ $course->categories->first()->name }}
                </span>
                @endif
                @if($course->level ?? null)
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">
                    Level: {{ ucfirst($course->level) }}
                </span>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">
                {{ $course->title ?? 'Judul Kursus' }}
            </h1>

            @if(!empty($course->subtitle))
            <p class="mt-1 text-sm sm:text-base text-gray-600">
                {{ $course->subtitle }}
            </p>
            @endif

            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs sm:text-sm text-gray-600">
                <div class="inline-flex items-center gap-2">
                    <span class="font-semibold text-amber-600">{{ $ratingText }}</span>
                </div>
                <span class="hidden sm:inline-block w-px h-4 bg-gray-300"></span>
                <div class="inline-flex items-center gap-1">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Instruktur: <span class="font-semibold">{{ $author }}</span></span>
                </div>
                <span class="hidden sm:inline-block w-px h-4 bg-gray-300"></span>
                <div class="inline-flex items-center gap-1">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    <span>{{ $totalModules }} modul / {{ $totalLessons }} pelajaran</span>
                </div>
            </div>
        </div>

        {{-- Thumbnail mini (opsional) --}}
        @if($course->thumbnail_path)
        <div class="hidden sm:block">
            <div class="w-32 h-20 rounded-xl overflow-hidden border border-gray-100 shadow-sm">
                <img src="{{ $course->thumbnail_path }}" alt="Thumbnail" class="w-full h-full object-cover">
            </div>
        </div>
        @endif
    </div>
</section>