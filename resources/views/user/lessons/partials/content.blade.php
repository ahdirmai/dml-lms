@if($lesson->kind === 'youtube' || $lesson->kind === 'gdrive')
<article class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 overflow-hidden">
    <div class="relative bg-black/80 aspect-video">
        {{-- YouTube preferensi pertama (jika ada) --}}
        @if($ytId)
        @if($youtubeEmbedAllowed && !empty($youtubeEmbedSrc))
        <iframe id="lesson-iframe" class="w-full h-full" src="{{ $youtubeEmbedSrc }}" title="YouTube video player"
            frameborder="0"
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
        <iframe id="lesson-iframe" class="w-full h-full" src="{{ $gdriveEmbedSrc }}" allow="autoplay"></iframe>

        @else
        <div class="w-full h-full flex items-center justify-center text-gray-300 text-sm sm:text-base px-4">
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
            {{-- resource tambahan --}}
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