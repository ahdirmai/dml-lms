{{-- resources/views/user/courses/partials/sidebar.blade.php --}}
@props([
'pct' => 0,
'barColor' => 'bg-brand',
'status' => 'Not Started',
'statusBadgeClass' => 'text-gray-600 bg-gray-100',
'statusTextClass' => 'text-gray-600',
'totalModules' => 0,
'ctaHref' => '#',
'ctaLabel' => 'Mulai Belajar',

// NEW: gate pretest
'pretestGateActive'=> false,

// optional: info kursus
'course' => null,
'jenis' => 'E-learning Mandiri',
'estimatedDuration'=> null,
'validUntil' => null,

// optional: quick actions pre/post/review
'courseId' => null,
'hasPreTest' => false,
'hasPostTest' => false,
'preDone' => false,
'postDone' => false,
'canReview' => false,
])

<aside class="lg:sticky lg:top-20 space-y-4">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-xl border border-gray-100">
        {{-- STATUS --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $statusBadgeClass }}">
                    <span class="w-2.5 h-2.5 rounded-full bg-current"></span>
                </span>
                <div>
                    <p class="text-xs text-gray-500">Status Kursus</p>
                    <p class="text-sm font-semibold {{ $statusTextClass }}">{{ $status }}</p>
                </div>
            </div>
        </div>

        {{-- PROGRESS MINI --}}
        <div class="mt-3 mb-4">
            <p class="text-xs text-gray-500 mb-1">
                Progres Anda
            </p>
            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-300" style="width: {{ $pct }}%">
                </div>
            </div>
            <div class="mt-1 flex justify-between text-[11px] text-gray-500">
                <span>{{ $pct }}% selesai</span>
                <span>{{ $totalModules }} modul</span>
            </div>
        </div>

        @php
        $ctaDisabled = $pretestGateActive;
        @endphp

        {{-- CTA utama (disable kalau pretest belum tercapai) --}}
        <a @if($ctaDisabled) href="javascript:void(0)" @else href="{{ $ctaHref }}" @endif class="w-full inline-flex items-center justify-center font-semibold py-2.5 rounded-xl text-sm transition shadow
                   {{ $ctaDisabled
                        ? 'bg-gray-300 text-gray-600 cursor-not-allowed pointer-events-none'
                        : 'bg-brand hover:brightness-95 text-white' }}" @if($ctaDisabled)
            title="Selesaikan pre-test untuk membuka materi." @endif>
            {{ $ctaLabel }}
        </a>

        {{-- Aksi cepat Pre-Test / Post-Test / Review (opsional) --}}
        @if($courseId && ($hasPreTest || $hasPostTest || $canReview))
        <div class="mt-3 space-y-1.5">
            <p class="text-[11px] font-semibold text-gray-500">Aksi Cepat</p>
            <div class="flex flex-wrap gap-1.5">
                @if($hasPreTest)
                <button type="button" onclick="window.TestFlow?.startPreTest('{{ $courseId }}')" class="inline-flex items-center px-3 py-1.5 rounded-full border text-[11px]
                                       {{ $preDone
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                         {{ $preDone ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                    Pre-Test
                </button>
                @endif

                @if($hasPostTest)
                <button type="button" onclick="window.TestFlow?.startPostTest('{{ $courseId }}')"
                    class="inline-flex items-center px-3 py-1.5 rounded-full border text-[11px]
                                       {{ $postDone
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 bg-emerald-500"></span>
                    Post-Test
                </button>
                @endif

                @if($canReview)
                <button type="button" onclick="window.TestFlow?.openReview('{{ $courseId }}')" class="inline-flex items-center px-3 py-1.5 rounded-full border text-[11px]
                                       border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100">
                    ★ Review
                </button>
                @endif
            </div>
        </div>
        @endif

        <hr class="my-4">

        {{-- INFO KURSUS SINGKAT --}}
        <div class="space-y-2 text-xs text-gray-600">
            <p class="font-semibold text-gray-800">Info Singkat</p>

            <p>
                Jenis:
                <span class="font-medium text-gray-800">
                    {{ $jenis ?? ($slot ?? 'E-learning Mandiri') }}
                </span>
            </p>

            @php
            $est = $estimatedDuration ?? optional($course)->estimated_duration;
            $valid = $validUntil ?? optional($course)->valid_until;
            @endphp

            @if(!empty($est))
            <p>Estimasi:
                <span class="font-medium text-gray-800">{{ $est }}</span>
            </p>
            @endif

            @if(!empty($valid))
            <p>Berlaku sampai:
                <span class="font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($valid)->format('d M Y') }}
                </span>
            </p>
            @endif
        </div>
    </div>
</aside>