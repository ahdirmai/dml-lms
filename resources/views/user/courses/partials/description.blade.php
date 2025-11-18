{{-- resources/views/user/courses/partials/description.blade.php --}}
@props(['course'])

<section class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-5 sm:p-6">
    <h3 class="text-lg sm:text-xl font-bold text-brand mb-2">Tentang Kursus Ini</h3>
    <p class="text-sm sm:text-base text-gray-700 leading-relaxed">
        {{ $course->description ?? 'Deskripsi kursus belum tersedia.' }}
    </p>

    @if(!empty($course->learning_objectives) && is_array($course->learning_objectives))
    <div class="mt-4">
        <p class="text-sm font-semibold text-gray-800 mb-1.5">Setelah menyelesaikan kursus ini, Anda akan dapat:</p>
        <ul class="list-disc list-inside text-sm text-gray-700 space-y-0.5">
            @foreach($course->learning_objectives as $obj)
            <li>{{ $obj }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</section>