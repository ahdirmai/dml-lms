{{-- resources/views/instructor/courses/create-builder.blade.php --}}
@extends('layouts.builder')

@section('title', 'Create New Course - DML Learning Management System')

@push('styles')
<style>
    .course-nav-item {
        padding: .75rem;
        margin-bottom: .5rem;
        border-radius: .5rem;
        transition: background-color .2s;
        cursor: pointer
    }

    .course-nav-item:hover {
        background-color: #ebf5fb
    }

    .course-nav-item.active {
        background-color: #3498db;
        color: #fff;
        font-weight: 600
    }

    .course-nav-item.active .text-gray-500 {
        color: #fff
    }

    .drag-handle {
        cursor: grab
    }

    .disabled-overlay {
        opacity: .5;
        pointer-events: none
    }
</style>
@endpush

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@php
$isEditMode = isset($lessonToEdit) && $lessonToEdit;
$SHOW_PRE = old('has_pretest', isset($course) ? (int)($course->has_pretest ?? 0) : 0);
$SHOW_POST = old('has_posttest', isset($course) ? (int)($course->has_posttest ?? 0) : 0);
@endphp

<aside class="w-80 bg-white p-6 shadow-xl flex flex-col fixed h-full overflow-y-auto">
    <div class="flex items-center mb-6">
        <a href="{{ route('instructor.courses.index') }}" class="text-gray-500 hover:text-primary-accent mr-3"
            title="Kembali ke daftar">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-xl font-bold text-gray-800">Back To Course Management</h1>
    </div>

    <h3 class="text-sm uppercase font-bold text-gray-500 mb-2 border-b pb-1">Pengaturan</h3>
    {{-- Pengaturan --}}
    <div id="nav-settings" class="course-nav-item {{ $isEditMode ? '' : 'active' }}" data-content="settings"
        data-type="setting" onclick="loadSettingsView()">
        <p>Pengaturan Kursus</p>
    </div>

    {{-- Pretest (muncul jika ON & course ada) --}}
    @if(isset($course) && $SHOW_PRE)
    <div id="nav-pretest" class="course-nav-item" onclick="showPretestView()">
        <p>Pengaturan Pretest</p>
    </div>
    @endif

    {{-- Posttest (muncul jika ON & course ada) --}}
    @if(isset($course) && $SHOW_POST)
    <div id="nav-posttest" class="course-nav-item" onclick="showPosttestView()">
        <p>Pengaturan Posttest</p>
    </div>
    @endif

    <h3 class="text-sm uppercase font-bold text-gray-500 mb-2 border-b pb-1">Struktur Konten</h3>
    <div id="content-structure" class="flex-grow space-y-2 @empty($course) disabled-overlay @endempty">
        @isset($course)
        @foreach($course->modules as $module)
        <div class="module-container bg-gray-50 p-3 rounded-lg border border-gray-200"
            data-module-id="{{ $module->id }}">
            <form action="{{ route('instructor.modules.update', $module->id) }}" method="POST" class="module-update-form"
                data-type="update">
                @csrf
                @method('PATCH')
                <div class="module-header flex justify-between items-center text-gray-700 font-semibold mb-2">
                    <span class="module-name-display">{{ $module->title }}</span>
                    <div class="flex items-center gap-3">
                        <button type="button" class="rename-module-btn text-gray-600 text-sm hover:underline"
                            onclick="openRenameModal('{{ $module->id }}', '{{ $module->title }}')">Ubah Judul</button>
                        <button type="button" class="add-lesson-btn text-primary-accent text-sm hover:underline"
                            onclick="loadLessonCreateView('{{ $module->id }}')">Tambah Pelajaran</button>
                    </div>
                </div>
            </form>

            <div class="lesson-list space-y-1">
                @foreach($module->lessons as $lesson)
                @php
                $isActive = $isEditMode && $lesson->id === $lessonToEdit->id;
                $badgeColor = ($lesson->kind === 'quiz') ? 'text-red-500' : 'text-gray-500';
                $badgeText = match($lesson->kind){'quiz'=>'Quiz','youtube'=>'Video','gdrive'=>'File',default=>'Draft'};
                @endphp
                <div class="course-nav-item lesson-item bg-white shadow-sm flex items-center justify-between text-sm {{ $isActive ? 'active' : '' }}"
                    data-type="lesson" data-lesson-id="{{ $lesson->id }}" data-module-id="{{ $module->id }}"
                    data-lesson-title="{{ e($lesson->title) }}" data-lesson-description="{{ e($lesson->description) }}"
                    data-lesson-kind="{{ e($lesson->kind) }}" data-lesson-url="{{ e($lesson->content_url) }}"
                    data-lesson-duration="{{ $lesson->duration_seconds }}"
                    data-lesson-update-url="{{ route('instructor.lessons.update', $lesson->id) }}">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500 drag-handle" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <span class="lesson-title-label">{{ $lesson->title }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-xs {{ $badgeColor }}">{{ $badgeText }}</span>
                        <form action="{{ route('instructor.lessons.destroy', $lesson->id) }}" method="POST"
                            onsubmit="return confirm('Yakin hapus pelajaran ini?')" class="ml-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 js-stop">Hapus</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            <form action="{{ route('instructor.modules.destroy', $module->id) }}" method="POST"
                onsubmit="return confirm('Yakin hapus modul ini beserta semua pelajaran di dalamnya?')" class="mt-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus Modul</button>
            </form>
        </div>
        @endforeach
        @endisset
    </div>

    <button type="button" id="add-module-btn"
        class="w-full bg-secondary-highlight hover:bg-[#25A65D] text-white font-bold py-3 rounded-xl text-md mt-6 shadow-md transition disabled:bg-gray-400"
        onclick="openModuleCreateModal()" {{ isset($course) ? '' : 'disabled' }}>
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Modul Baru
    </button>
</aside>

<main class="ml-80 flex-1 p-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800" id="editor-title">{{ $isEditMode ? 'Edit Pelajaran' : 'Pengaturan
            Kursus Baru' }}</h2>
        <div class="flex space-x-3" id="header-buttons">
            <button type="submit" form="course-settings-form" id="save-draft-btn"
                class="bg-secondary-highlight hover:bg-[#25A65D] text-white font-bold py-2 px-6 rounded-xl shadow-md transition {{ $isEditMode ? 'hidden' : '' }}">
                Simpan Pengaturan
            </button>
            @isset($course)
            <form id="publish-form" action="{{ route('instructor.courses.publish', $course->id) }}" method="POST"
                style="display:inline;">
                @csrf
                <button type="submit" id="publish-btn"
                    class="bg-primary-accent hover:bg-[#2e82c8] text-white font-bold py-2 px-6 rounded-xl shadow-md transition {{ ($course->status !== 'draft' || !$course->modules->count()) ? 'disabled:bg-gray-400' : '' }}"
                    {{ ($course->status !== 'draft' || !$course->modules->count()) ? 'disabled' : '' }}>
                    Publikasikan
                </button>
            </form>
            @endisset
        </div>
    </div>

    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if (session('error'))
    <div class="bg-green-100 border border-green-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
        @if (session('import_errors'))
        <ul class="list-disc list-inside mt-2">
            @foreach (session('import_errors') as $line)
            <li>{{ $line }}</li>
            @endforeach
        </ul>
        @endif
    </div>


    @endif

    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Gagal!</strong>
        <span class="block sm:inline">Terdapat kesalahan pada input Anda:</span>
        <ul class="list-disc ml-5 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div id="editor-content" class="bg-white p-8 rounded-2xl shadow-lg min-h-[70vh]">
        {{-- VIEW 1: SETTINGS --}}
        <div id="content-settings" class="{{ $isEditMode ? 'hidden' : '' }}">
            <h3 class="text-2xl font-bold text-primary-accent mb-6">Pengaturan Umum</h3>

            <form id="course-settings-form"
                action="{{ isset($course) ? route('instructor.courses.update', $course->id) : route('instructor.courses.store') }}"
                method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @isset($course) @method('PATCH') @endisset

                <input type="hidden" id="active_course_id" name="__course_id" value="{{ $course->id ?? '' }}">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Kursus</label>
                    <input type="text" id="course_title" name="title" placeholder="Masukkan judul yang menarik..."
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent"
                        value="{{ old('title', $course->title ?? '') }}" required />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Subjudul (opsional)</label>
                    <input type="text" id="course_subtitle" name="subtitle" placeholder="Subjudul singkat..."
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent"
                        value="{{ old('subtitle', $course->subtitle ?? '') }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Kursus</label>
                    <textarea rows="5" id="course_description" name="description"
                        placeholder="Jelaskan apa yang akan dipelajari siswa..."
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent"
                        required>{{ old('description', $course->description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                    <select name="category_id" id="category_id"
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent"
                        required>
                        <option value="" disabled selected>Pilih kategori</option>
                        @php $currentCategoryId = $course->categories[0]->id ?? old('category_id'); @endphp
                        @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($currentCategoryId===$cat->id)>{{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Level</label>
                    {{-- tetap "level" (controller fallback ke difficulty) --}}
                    <select name="level" id="level"
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent">
                        @php $currentLevel = $course->difficulty ?? old('level', 'intermediate'); @endphp
                        <option value="beginner" @selected($currentLevel==='beginner' )>Beginner</option>
                        <option value="intermediate" @selected($currentLevel==='intermediate' )>Intermediate</option>
                        <option value="advanced" @selected($currentLevel==='advanced' )>Advanced</option>
                    </select>
                </div>
                {{-- <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instruktur</label>
                    <select name="instructor_id" id="instructor_id"
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent"
                        required>
                        <option value="" disabled selected>Pilih instruktur</option>
                        @php $currentInstructorId = $course->instructor_id ?? old('instructor_id'); @endphp
                        @foreach ($instructors as $usr)
                        <option value="{{ $usr->id }}" @selected($currentInstructorId===$usr->id)>{{ $usr->name }}
                        </option>
                        @endforeach
                    </select>
                </div> --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Thumbnail</label>
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent" />
                </div>

                {{-- ======== TOGGLES: PRE/POST & REQUIRE ======== --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center justify-between p-4 rounded-xl border bg-white">
                        <div>
                            <p class="font-semibold text-gray-800">Aktifkan Pretest</p>
                            <p class="text-sm text-gray-500">Jika aktif, kursus memiliki Pretest.</p>
                        </div>
                        @php $preOn = old('has_pretest', isset($course) ? (int)($course->has_pretest ?? 0) : 0); @endphp
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="has_pretest" id="has_pretest" class="sr-only peer" value="1"
                                @checked($preOn)>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700"><span id="label_has_pretest">{{ $preOn
                                    ? 'ON' : 'OFF' }}</span></span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl border bg-white">
                        <div>
                            <p class="font-semibold text-gray-800">Aktifkan Posttest</p>
                            <p class="text-sm text-gray-500">Jika aktif, kursus memiliki Posttest.</p>
                        </div>
                        @php $postOn = old('has_posttest', isset($course) ? (int)($course->has_posttest ?? 0) : 0);
                        @endphp
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="has_posttest" id="has_posttest" class="sr-only peer" value="1"
                                @checked($postOn)>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700"><span id="label_has_posttest">{{
                                    $postOn ? 'ON' : 'OFF' }}</span></span>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div class="flex items-center justify-between p-4 rounded-xl border bg-white">
                        <div>
                            <p class="font-semibold text-gray-800">Aktifkan Due Date</p>
                            <p class="text-sm text-gray-500">Jika aktif, kursus memiliki Due Date.</p>
                        </div>
                        @php $preOn = old('using_due_date', isset($course) ? (int)($course->using_due_date ?? 0) : 0);
                        @endphp
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="using_due_date" id="using_due_date" class="sr-only peer"
                                value="1" @checked($preOn)>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700"><span id="label_using_due_date">{{
                                    $preOn
                                    ? 'ON' : 'OFF' }}</span></span>
                        </label>
                    </div>
                </div>

                <div class="p-4 rounded-xl border bg-white">
                    @php $requireBefore = old('require_pretest_before_content', isset($course) ?
                    (int)($course->require_pretest_before_content ?? 0) : 0); @endphp
                    <label class="inline-flex items-center gap-3">
                        <input type="checkbox" name="require_pretest_before_content" id="require_pretest_before_content"
                            class="rounded-md" value="1" @checked($requireBefore)>
                        <span class="text-sm text-gray-700">Wajib lulus Pretest sebelum mengakses konten</span>
                    </label>
                </div>

                {{-- flags untuk guard publish (di-set saat form pre/post disimpan) --}}
                <input type="hidden" id="pretest_ready" name="pretest_ready"
                    value="{{ isset($course) && method_exists($course,'pretest')  && $course->pretest  ? 1 : 0 }}">
                <input type="hidden" id="posttest_ready" name="posttest_ready"
                    value="{{ isset($course) && method_exists($course,'posttest') && $course->posttest ? 1 : 0 }}">
                {{-- ======== END TOGGLES ======== --}}

                <hr class="border-t border-gray-100" />
            </form>
        </div>

        {{-- VIEW 2: CREATE/UPDATE LESSON FORM --}}
        <div id="content-lesson-create" class="{{ $isEditMode ? '' : 'hidden' }}">
            <h3 class="text-2xl font-bold text-primary-accent mb-6" id="lesson-create-title">
                {{ $isEditMode ? 'Edit Pelajaran: ' . ($lessonToEdit->title ?? '') : 'Tambah Pelajaran Baru' }}
            </h3>
            <form action="{{ $isEditMode ? route('instructor.lessons.update', $lessonToEdit->id) : '' }}" method="POST"
                id="lesson-create-form" class="space-y-6"
                data-store-template="{{ route('instructor.lessons.store', ['module' => 'MODULE_ID']) }}">
                @csrf
                @if($isEditMode) @method('PATCH') @else
                <input type="hidden" name="module_id" id="lesson_target_module_id" value="">
                @endif

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Pelajaran</label>
                    <input type="text" name="title" placeholder="Judul Pelajaran..." required
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent"
                        value="{{ old('title', $lessonToEdit->title ?? '') }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Singkat</label>
                    <textarea name="description" rows="3" placeholder="Deskripsi singkat pelajaran..."
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent">{{ old('description', $lessonToEdit->description ?? '') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Durasi (Detik)</label>
                    <input type="number" name="duration_seconds" min="0" placeholder="0"
                        class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent"
                        value="{{ old('duration_seconds', $lessonToEdit->duration_seconds ?? 0) }}" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Konten</label>
                    <select id="lesson_content_type" name="kind"
                        class="p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent w-1/3"
                        required>
                        @php $currentKind = old('kind', $lessonToEdit->kind ?? 'youtube'); @endphp
                        <option value="youtube" @selected($currentKind==='youtube' )>Video (YouTube)</option>
                        <option value="gdrive" @selected($currentKind==='gdrive' )>File (G-Drive/Upload)</option>
                        {{-- <option value="quiz">Quiz</option> --}} {{-- DIHILANGKAN sesuai requirement --}}
                    </select>
                </div>

                <div id="lesson_content_dynamic_area" class="space-y-4 p-4 border rounded-xl bg-gray-50">
                    <p class="text-gray-500">Pilih tipe konten untuk menampilkan input terkait.</p>
                </div>

                <div class="flex justify-between items-center pt-4 border-t">
                    <button type="button" onclick="loadSettingsView()"
                        class="text-gray-500 hover:text-gray-700 font-medium">← Kembali ke Pengaturan</button>
                    <button type="submit"
                        class="bg-secondary-highlight hover:bg-[#25A65D] text-white font-bold py-2 px-6 rounded-xl text-sm shadow-md transition">Simpan
                        & Selesai</button>
                </div>
            </form>
        </div>

        {{-- VIEW 3: PRETEST --}}
        @if(isset($course))
        <div id="content-pretest" class="hidden">
            @include('instructor.pages.courses.partials._quiz-form', [
            'course' => $course,
            'kind' => \App\Models\Lms\Quiz::KIND_PRETEST,
            'store_route' => route('instructor.courses.pretest.store', $course->id),
            'existing' => method_exists($course,'pretest') ? $course->pretest : null,
            'routePrefix' => 'instructor',
            ])
        </div>

        <div id="content-posttest" class="hidden">
            @include('instructor.pages.courses.partials._quiz-form', [
            'course' => $course,
            'kind' => \App\Models\Lms\Quiz::KIND_POSTTEST,
            'store_route' => route('instructor.courses.posttest.store', $course->id),
            'existing' => method_exists($course,'posttest') ? $course->posttest : null,
            'routePrefix' => 'instructor',
            ])
        </div>
        @endif

    </div> {{-- end #editor-content --}}
</main>

{{-- MODAL: CREATE MODULE --}}
<div id="module-create-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden z-50 justify-center items-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Nama Modul Baru</h3>
        <form action="{{ isset($course) ? route('instructor.modules.store', $course->id) : '#' }}" method="POST"
            id="module-create-form">
            @csrf
            <div class="mb-4">
                <label for="module_title_input" class="block text-sm font-medium text-gray-700 mb-2">Judul Modul</label>
                <input type="text" id="module_title_input" name="title" placeholder="Tulis di sini..."
                    class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent"
                    required>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-xl transition hover:bg-gray-300"
                    onclick="closeModuleCreateModal()">Batal</button>
                <button type="submit"
                    class="bg-primary-accent text-white font-bold py-2 px-4 rounded-xl transition hover:bg-[#2e82c8]">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: RENAME MODULE --}}
<div id="module-rename-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden z-50 justify-center items-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Ubah Judul Modul</h3>
        <form action="#" method="POST" id="module-rename-form">
            @csrf
            @method('PATCH')
            <div class="mb-4">
                <label for="module_rename_title_input" class="block text-sm font-medium text-gray-700 mb-2">Judul
                    Modul</label>
                <input type="text" id="module_rename_title_input" name="title" placeholder="Tulis di sini..."
                    class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent"
                    required>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-xl transition hover:bg-gray-300"
                    onclick="closeRenameModal()">Batal</button>
                <button type="submit"
                    class="bg-primary-accent text-white font-bold py-2 px-4 rounded-xl transition hover:bg-[#2e82c8]">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /* ===========================
 *  Util & SweetAlert helpers
 * =========================== */
const token       = document.querySelector('meta[name="csrf-token"]')?.content;
const swalError   = async (text, title='Gagal')    => await Swal.fire({icon:'error',   title, text, confirmButtonText:'OK'});
const swalSuccess = async (text, title='Berhasil') => await Swal.fire({icon:'success', title, text, confirmButtonText:'OK'});
const swalInfo    = async (text, title='Info')     => await Swal.fire({icon:'info',    title, text, confirmButtonText:'OK'});

/* ===========================
 *  DOM refs (assigned on DOMContentLoaded)
 * =========================== */
let contentSettings, contentLessonCreate, contentPretest, contentPosttest, contentStructure;
let lessonCreateForm, lessonContentType, lessonContentArea;
let saveDraftBtn, publishBtn, publishForm;
let renameModal, renameForm, renameInput;
let hasPre, hasPost, lblPre, lblPost, preReady, postReady;

/* ===========================
 *  Small helpers
 * =========================== */
function ensurePatch(formEl){
  let m = formEl.querySelector('input[name="_method"]');
  if (!m) { m = document.createElement('input'); m.type='hidden'; m.name='_method'; formEl.appendChild(m); }
  m.value = 'PATCH';
}
function removePatch(formEl){
  const m = formEl.querySelector('input[name="_method"]');
  if (m) m.remove();
}
function setVal(selector, val){
  const el = document.querySelector(selector);
  if (el) el.value = val ?? '';
}
function markOnlyActiveLesson(el){
  document.querySelectorAll(".course-nav-item.lesson-item.active").forEach(x=>x.classList.remove("active"));
  if (el) el.classList.add("active");
}

/* ===========================
 *  Header button visibility
 * =========================== */
function setHeaderForLessonMode(){
  if (saveDraftBtn) saveDraftBtn.classList.add('hidden');
  if (publishBtn)   publishBtn.classList.add('hidden');
}

function setHeaderForSettingsMode(){
  if (saveDraftBtn) saveDraftBtn.classList.remove('hidden');
  if (publishBtn)   publishBtn.classList.remove('hidden');
}

// function setHeaderForSettingQuizsMode(){
// if (saveDraftBtn) saveDraftBtn.classList.remove('hidden');
// if (publishBtn) publishBtn.classList.remove('hidden');
// }

/* ===========================
 *  Sidebar: set active state
 *   - id yang dikenali: nav-settings, nav-pretest, nav-posttest
 * =========================== */
function setActiveSidebar(targetId){
  ['nav-settings','nav-pretest','nav-posttest'].forEach(id=>{
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
  });
  if (targetId){
    const tgt = document.getElementById(targetId);
    if (tgt) tgt.classList.add('active');
  }
}

/* ===========================
 *  View switchers
 * =========================== */
function hideAll() {
  contentSettings?.classList.add("hidden");
  contentLessonCreate?.classList.add("hidden");
  contentPretest?.classList.add("hidden");
  contentPosttest?.classList.add("hidden");
}
function showSettingsView(){
  document.getElementById("editor-title").textContent = "Pengaturan Kursus Baru";
  hideAll(); contentSettings?.classList.remove("hidden");
  setHeaderForSettingsMode();
  setActiveSidebar('nav-settings');
  markOnlyActiveLesson(null); // <— pastikan lesson tidak aktif bersamaan
}
function showLessonView(){
  hideAll(); contentLessonCreate?.classList.remove("hidden");
  setHeaderForLessonMode();

}
function showPretestView(){
  document.getElementById("editor-title").textContent = "Pengaturan Pretest";
  hideAll(); contentPretest?.classList.remove("hidden");
  setHeaderForSettingsMode();
  setActiveSidebar('nav-pretest');
  setHeaderForLessonMode();
  markOnlyActiveLesson(null); // <— clear lesson active
}
function showPosttestView(){
  document.getElementById("editor-title").textContent = "Pengaturan Posttest";
  hideAll(); contentPosttest?.classList.remove("hidden");
  setHeaderForSettingsMode();
  setHeaderForLessonMode();
  setActiveSidebar('nav-posttest');
  markOnlyActiveLesson(null); // <— clear lesson active
}
window.loadSettingsView = showSettingsView;
window.showPretestView  = showPretestView;
window.showPosttestView = showPosttestView;

/* ===========================
 *  Lesson dynamic fields renderer
 *   (quiz dihilangkan sesuai requirement)
 * =========================== */
function renderLessonFields(kind, url){
  const u = url || '';
  lessonContentArea.innerHTML = '';
  if (kind === 'youtube') {
    lessonContentArea.innerHTML = `
      <label class="block text-sm font-semibold text-gray-700 mb-2">Link YouTube</label>
      <input type="url" name="content_url" placeholder="https://www.youtube.com/watch?v=..." class="w-full p-3 border border-gray-300 rounded-xl" value="${u}" required>`;
  } else if (kind === 'gdrive') {
    lessonContentArea.innerHTML = `
      <label class="block text-sm font-semibold text-gray-700 mb-2">Link G-Drive (PDF/File)</label>
      <input type="url" name="content_url" placeholder="https://drive.google.com/file/d/.../view" class="w-full p-3 mb-2 border border-gray-300 rounded-xl" value="${u}" required>
      <p class="text-xs text-gray-500">ID file akan diambil otomatis.</p>`;
  } else {
    lessonContentArea.innerHTML = `<p class="text-gray-500">Pilih tipe konten untuk menampilkan input terkait.</p>`;
  }
}

/* ===========================
 *  Public: loadLessonCreateView(moduleId)
 *   - Create Lesson flow
 * =========================== */
window.loadLessonCreateView = function(moduleId) {
  const moduleName = document.querySelector(`[data-module-id="${moduleId}"] .module-name-display`)?.textContent.trim() || 'Modul';
  document.getElementById("editor-title").textContent = `Tambah Pelajaran untuk: ${moduleName}`;

  hideAll();
  contentLessonCreate?.classList.remove("hidden");
  setHeaderForLessonMode();

  if (lessonCreateForm) {
    lessonCreateForm.reset();
    removePatch(lessonCreateForm);

    const moduleHidden = document.getElementById("lesson_target_module_id");
    if (moduleHidden) moduleHidden.value = moduleId;

    const tpl = lessonCreateForm.getAttribute('data-store-template'); // .../MODULE_ID/...
    if (tpl) lessonCreateForm.action = tpl.replace('MODULE_ID', moduleId);

    const kind = lessonContentType?.value || 'youtube';
    renderLessonFields(kind, '');
  }

  setActiveSidebar(null);       // <— clear active item di sidebar (settings/pre/post)
  markOnlyActiveLesson(null);   // <— pastikan tidak ada lesson lain aktif
  contentLessonCreate?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

/* ===========================
 *  Init & Events
 * =========================== */
document.addEventListener("DOMContentLoaded", () => {
  // assign DOM refs
  contentSettings     = document.getElementById("content-settings");
  contentLessonCreate = document.getElementById("content-lesson-create");
  contentPretest      = document.getElementById("content-pretest");
  contentPosttest     = document.getElementById("content-posttest");
  contentStructure    = document.getElementById("content-structure");

  lessonCreateForm    = document.getElementById("lesson-create-form");
  lessonContentType   = document.getElementById("lesson_content_type");
  lessonContentArea   = document.getElementById("lesson_content_dynamic_area");

  saveDraftBtn        = document.getElementById('save-draft-btn');
  publishBtn          = document.getElementById('publish-btn');
  publishForm         = document.getElementById('publish-form');

  renameModal         = document.getElementById('module-rename-modal');
  renameForm          = document.getElementById('module-rename-form');
  renameInput         = document.getElementById('module_rename_title_input');

  hasPre              = document.getElementById('has_pretest');
  hasPost             = document.getElementById('has_posttest');
  lblPre              = document.getElementById('label_has_pretest');
  lblPost             = document.getElementById('label_has_posttest');
  preReady            = document.getElementById('pretest_ready');
  postReady           = document.getElementById('posttest_ready');

  const activeCourseId = document.getElementById("active_course_id")?.value || '';
  const IS_EDIT_MODE   = {{ isset($isEditMode) && $isEditMode ? 'true' : 'false' }};
  const LESSON_DATA    = {!! isset($isEditMode) && $isEditMode ? json_encode($lessonToEdit) : 'null' !!};

  // Toggle label ON/OFF
  if (hasPre)  hasPre.addEventListener('change',  () => lblPre.textContent  = hasPre.checked  ? 'ON' : 'OFF');
  if (hasPost) hasPost.addEventListener('change', () => lblPost.textContent = hasPost.checked ? 'ON' : 'OFF');

  // Guard Publish: wajib isi pre/post jika ON
  if (publishForm && publishBtn) {
    publishForm.addEventListener('submit', (ev) => {
      const needsPre  = hasPre && hasPre.checked;
      const needsPost = hasPost && hasPost.checked;

      const okPre  = !needsPre  || (preReady  && preReady.value  === '1');
      const okPost = !needsPost || (postReady && postReady.value === '1');

      if (!okPre || !okPost) {
        ev.preventDefault();
        const missing = [];
        if (needsPre && !okPre)  missing.push('Pretest');
        if (needsPost && !okPost) missing.push('Posttest');
        Swal.fire({
          icon:'error',
          title:'Belum Lengkap',
          html:`Anda mengaktifkan <b>${missing.join(' & ')}</b>.<br>Silakan isi & simpan terlebih dahulu sebelum publish.`
        });
        return false;
      }
    });
  }

  // Sidebar: klik lesson => masuk mode edit lesson
  if (contentStructure) {
    contentStructure.addEventListener('click', (e)=>{
      const stopBtn = e.target.closest('.js-stop');
      if (stopBtn){ e.stopPropagation(); return; }

      const item = e.target.closest('.course-nav-item.lesson-item');
      if (!item) return;

      const title = item.dataset.lessonTitle || '';
      const desc  = item.dataset.lessonDescription || '';
      const kind  = item.dataset.lessonKind || 'youtube';
      const url   = item.dataset.lessonUrl || '';
      const dur   = item.dataset.lessonDuration || 0;
      const upUrl = item.dataset.lessonUpdateUrl; if(!upUrl) return;

      document.getElementById("editor-title").textContent = `Edit Pelajaran: ${title}`;
      showLessonView();

      setActiveSidebar(null);        // <— clear active item di sidebar (settings/pre/post)
      markOnlyActiveLesson(item);    // <— hanya lesson ini yang aktif

      lessonCreateForm.action = upUrl;
      ensurePatch(lessonCreateForm);
      setVal('#lesson-create-form input[name="title"]', title);
      setVal('#lesson-create-form textarea[name="description"]', desc);
      setVal('#lesson-create-form input[name="duration_seconds"]', dur);
      lessonContentType.value = kind;
      renderLessonFields(kind, url);
    });
  }

  // Ganti input dinamis saat kind berubah
  if (lessonContentType) {
    lessonContentType.addEventListener('change', (e)=> renderLessonFields(e.target.value));
  }

  /* ===========================
   *  Modal create/rename module
   * =========================== */
  window.openModuleCreateModal = ()=>{
    const m = document.getElementById("module-create-modal");
    m.classList.remove('hidden'); m.classList.add('flex');
    document.getElementById("module_title_input")?.focus();
  };
  window.closeModuleCreateModal = ()=>{
    const m = document.getElementById("module-create-modal");
    m.classList.add('hidden'); m.classList.remove('flex');
  };

  window.openRenameModal = (moduleId,currentTitle)=>{
    const renameUrl = '{{ route('instructor.modules.update', ['module' => ':id']) }}'.replace(':id', moduleId);
    renameForm.action = renameUrl;
    renameForm.dataset.moduleId = moduleId;
    renameInput.value = currentTitle;
    renameModal.classList.remove('hidden'); renameModal.classList.add('flex');
    renameInput.focus();
  };
  window.closeRenameModal = ()=>{
    renameModal.classList.add('hidden'); renameModal.classList.remove('flex');
  };

  if (renameForm){
    renameForm.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const moduleId = renameForm.dataset.moduleId; if (!moduleId) return;

      const fd = new FormData(renameForm); fd.set('_method','PATCH');
      const submitBtn = renameForm.querySelector('button[type="submit"]');
      const prevText  = submitBtn?.textContent;
      if (submitBtn){ submitBtn.disabled = true; submitBtn.textContent = 'Menyimpan...'; }

      try{
        const res = await fetch(renameForm.action, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
          body: fd
        });

        if (!res.ok){
          let msg = 'Gagal menyimpan perubahan.';
          try{
            const j = await res.json();
            msg = j.message || (j.errors ? Object.values(j.errors).flat().join('\n') : msg);
          }catch(_){}
          await swalError(msg);
          return;
        }

        // Update UI optimistik
        const newTitle  = renameInput.value.trim();
        const container = document.querySelector(`.module-container[data-module-id="${moduleId}"]`);
        if (container){
          const titleSpan = container.querySelector('.module-name-display');
          if (titleSpan) titleSpan.textContent = newTitle;
        }

        closeRenameModal();
        await swalSuccess('Judul modul berhasil diperbarui.');
      }catch(err){
        console.error(err);
        await swalError('Terjadi kesalahan jaringan.');
      }finally{
        if (submitBtn){ submitBtn.disabled = false; submitBtn.textContent = prevText || 'Simpan'; }
      }
    });
  }



  /* ===========================
   *  Init
   * =========================== */
  if (activeCourseId){
    if (IS_EDIT_MODE && LESSON_DATA){
      // sedang edit lesson
      renderLessonFields(LESSON_DATA.kind, LESSON_DATA.content_url || '');
      const updateUrlTemplate = '{{ route('instructor.lessons.update', ['lesson' => ':id']) }}';
      lessonCreateForm.action = updateUrlTemplate.replace(':id', LESSON_DATA.id);
      ensurePatch(lessonCreateForm);
      setHeaderForLessonMode();

      setActiveSidebar(null);   // <— pastikan sidebar tidak aktif saat edit lesson dari server
    } else {
      // normal settings view
      if (!contentLessonCreate.classList.contains('hidden')) {
        renderLessonFields(lessonContentType.value);
        setHeaderForLessonMode();
        setActiveSidebar(null);
      } else {
        setHeaderForSettingsMode();
        setActiveSidebar('nav-settings');
      }
    }
  } else {
    showSettingsView();
  }

  // Expose ke partial _quiz-form:
  window.handleQuizSave = function(e, kind) {
    const titleEl = document.getElementById(`quiz_title_${kind}`);
    if (!titleEl || !titleEl.value.trim()) {
      e.preventDefault();
      Swal.fire({ icon:'warning', title:'Lengkapi Judul', text:`Judul ${kind} wajib diisi.` });
      return false;
    }
    if (kind === 'pretest' && preReady) preReady.value = 1;
    if (kind === 'posttest' && postReady) postReady.value = 1;
    return true;
  };

  // BACA tab dari URL lalu tampilkan view yang sesuai
(function initTabFromURL(){
  const params = new URLSearchParams(location.search);
  const tab = (params.get('tab') || '').toLowerCase();

  if (tab === 'pretest') {
    if (typeof showPretestView === 'function') showPretestView();
  } else if (tab === 'posttest') {
    if (typeof showPosttestView === 'function') showPosttestView();
  } else {
    if (typeof loadSettingsView === 'function') loadSettingsView(); // default
  }

  // Scroll ke anchor jika ada (mis. #questions)
  if (location.hash) {
    const target = document.querySelector(location.hash);
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
})();
});
</script>