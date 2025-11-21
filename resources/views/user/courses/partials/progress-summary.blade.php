{{-- resources/views/user/courses/partials/progress-summary.blade.php --}}
@props([
'pct' => 0,
'barColor' => 'bg-brand',
'status' => 'Not Started',
'statusBadgeClass' => 'text-gray-600 bg-gray-100',
'ctaHref' => '#',
'ctaLabel' => 'Mulai Belajar',
'totalModules' => 0,
'pretestGateActive'=> false,
'requirePretest' => false,

// tambahan untuk tombol test
'courseId' => null,
'hasPreTest' => false,
'hasPostTest' => false,
'preDone' => false,
'postDone' => false,
'canReview' => false,
])

<section class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <p class="text-[11px] font-extrabold text-gray-500 tracking-wider uppercase">
                Progres Kursus
            </p>
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-0.5">
                {{ $pct }}% Selesai
            </h3>
        </div>
        <span
            class="inline-flex items-center gap-2 text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusBadgeClass }}">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-current"></span>
            {{ $status }}
        </span>
    </div>

    <div class="space-y-2">
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
            <div class="{{ $barColor }} h-3 rounded-full transition-all duration-300"
                style="width: {{ max(2, $pct) }}%">
            </div>
        </div>
        <div class="flex justify-between text-[11px] text-gray-500">
            <span>{{ $totalModules }} modul</span>
            <span>{{ $pct }}% progres</span>
        </div>
    </div>

    {{-- CTA + tombol test di sampingnya --}}
    <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-2">
        {{-- Tombol utama belajar --}}
        @php
            $ctaDisabled = $requirePretest && $pretestGateActive;
        @endphp
        <a href="{{ $ctaDisabled ? '#' : $ctaHref }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow
                  {{ $ctaDisabled ? 'bg-gray-400 cursor-not-allowed pointer-events-none'
                                        : 'bg-brand hover:brightness-95' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
            {{ $ctaLabel }}
        </a>

        {{-- Tombol kecil untuk Pre/Post/Review --}}
        <div class="flex flex-wrap items-center gap-2 text-[11px]">
            @if($courseId)
            @if($hasPreTest && !$preDone)
            <button type="button" onclick="window.TestFlow?.startPreTest('{{ $courseId }}')"
                class="inline-flex items-center px-3 py-1.5 rounded-full border border-blue-200 bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2" />
                </svg>
                Pre-Test
            </button>
            @endif

            @if($hasPostTest && $preDone && !$postDone && $pct >= 100)
            <button type="button" onclick="window.TestFlow?.startPostTest('{{ $courseId }}')"
                class="inline-flex items-center px-3 py-1.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 font-semibold hover:bg-emerald-100">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Post-Test
            </button>
            @endif

            @if($canReview && $postDone)
            <button type="button" onclick="window.TestFlow?.openReview('{{ $courseId }}')"
                class="inline-flex items-center px-3 py-1.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700 font-semibold hover:bg-amber-100">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.383 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.383-2.46a1 1 0 00-1.175 0l-3.383 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.998 9.394c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z" />
                </svg>
                Review
            </button>
            @endif
            @endif
        </div>
    </div>

    <p class="mt-2 text-[11px] text-gray-500">
        Sistem akan mengingat pelajaran terakhir yang Anda buka.
        @if($pretestGateActive)
        <span class="text-amber-600 font-semibold"> Pre-test wajib diselesaikan sebelum materi dapat diakses.</span>
        @endif
    </p>
</section>