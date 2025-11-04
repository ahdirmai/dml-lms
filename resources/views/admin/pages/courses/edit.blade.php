<x-app-layout :title="'Edit Course — ' . $course->title">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dark leading-tight">Edit Course</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @include('admin.pages.courses._form', [
                'action' => route('admin.courses.update', $course),
                'method' => 'PUT',
                'course' => $course,
                'submitLabel' => 'Save Changes',
                'categories' => $categories, // ✅ sudah ada
                'instructors' => $instructors, // ✅ tambahkan ini
                ])

                <div class="mt-6 flex gap-2">
                    <a href="{{ route('admin.courses.builder', $course) }}"
                        class="inline-flex items-center justify-center font-semibold rounded-lg border px-4 py-2 text-sm text-dark hover:bg-soft">
                        Open Builder
                    </a>

                    {{-- Toggle Publish menggunakan update() + hidden fields yang memang divalidasi --}}
                    <form method="POST" action="{{ route('admin.courses.update',$course) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="title" value="{{ $course->title }}">
                        <input type="hidden" name="description" value="{{ $course->description }}">
                        <input type="hidden" name="category_id" value="{{ $course->category_id }}">
                        <input type="hidden" name="duration" value="{{ $course->duration }}">
                        {{-- thumbnail tak perlu dikirim ulang; biarkan controller mengabaikan jika tidak ada file baru
                        --}}
                        <input type="hidden" name="status"
                            value="{{ $course->status === 'published' ? 'draft' : 'published' }}">

                        <form method="POST" action="{{ route('admin.courses.toggle-status', $course) }}">
                            @csrf @method('PUT')
                            <button
                                class="inline-flex items-center justify-center font-semibold rounded-lg px-4 py-2 text-sm {{ $course->status==='published' ? 'bg-warning text-white' : 'bg-brand text-white' }}">
                                {{ $course->status === 'published' ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>