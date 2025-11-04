<x-app-layout :title="'Course Builder'">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dark leading-tight">Course Builder </h2>
    </x-slot>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Structure --}}
        <div class="lg:col-span-1 bg-white rounded-xl shadow p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold">Structure</h3>
                <button id="btnAddModule" class="px-3 py-1.5 text-sm bg-brand text-white rounded-lg">+ Module</button>
            </div>
            <div id="moduleList" data-course="{{ $course->id }}">
                {{-- server-rendered modules --}}
                @foreach($course->modules()->orderBy('position')->get() as $m)
                <div class="mb-2 border rounded-lg">
                    <div class="flex items-center justify-between p-2 bg-soft">
                        <button class="module-row text-left font-medium" data-module="{{ $m->id }}">{{ $m->title
                            }}</button>
                        <div class="flex gap-1">
                            <button class="btnEditModule text-xs px-2 py-1 border rounded"
                                data-id="{{ $m->id }}">Edit</button>
                            <button class="btnDeleteModule text-xs px-2 py-1 bg-danger text-white rounded"
                                data-id="{{ $m->id }}">Delete</button>
                        </div>
                    </div>
                    <div class="p-2 space-y-1" id="lessons-{{ $m->id }}">
                        @foreach($m->lessons()->orderBy('position')->get() as $l)
                        <div class="flex items-center justify-between px-2 py-1 border rounded lesson-row"
                            data-lesson="{{ $l->id }}">
                            <button class="text-left" data-open-lesson="{{ $l->id }}">{{ $l->title }}</button>
                            <div class="flex gap-1">
                                <button class="btnEditLesson text-xs px-2 py-1 border rounded"
                                    data-id="{{ $l->id }}">Edit</button>
                                <button class="btnDeleteLesson text-xs px-2 py-1 bg-danger text-white rounded"
                                    data-id="{{ $l->id }}">Delete</button>
                            </div>
                        </div>
                        @endforeach
                        <button class="btnAddLesson mt-2 text-xs px-2 py-1 border rounded" data-module="{{ $m->id }}">+
                            Lesson</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Right: Forms --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Course base form (uses _form above) --}}
            <div class="bg-white rounded-xl shadow p-4">
                <h3 class="font-semibold mb-2">Course</h3>
                <form id="courseForm" action="{{ route('admin.courses.update', $course) }}" method="POST"
                    enctype="multipart/form-data">
                    @method('PUT')
                    @include('admin.pages.courses._form', ['course'=>$course,
                    'categories'=>$categories,'instructors'=>$instructors])
                </form>
            </div>

            {{-- Module form --}}
            <div class="bg-white rounded-xl shadow p-4">
                <h3 class="font-semibold mb-2">Module</h3>
                <form id="moduleForm" onsubmit="return false;">
                    <input type="hidden" name="id" id="module_id">
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <div class="grid gap-3">
                        <div>
                            <label class="block text-sm font-medium">Title</label>
                            <input type="text" name="title" id="module_title" class="w-full rounded-xl border px-3 py-2"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Description</label>
                            <textarea name="description" id="module_description" rows="3"
                                class="w-full rounded-xl border px-3 py-2"></textarea>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button id="btnSaveModule" class="px-4 py-2 rounded-lg bg-brand text-white">Save Module</button>
                        <button id="btnResetModule" type="button" class="px-4 py-2 rounded-lg border">Reset</button>
                    </div>
                </form>
            </div>

            {{-- Lesson form --}}
            <div class="bg-white rounded-xl shadow p-4">
                <h3 class="font-semibold mb-2">Lesson</h3>
                <form id="lessonForm" onsubmit="return false;">
                    <input type="hidden" name="id" id="lesson_id">
                    <input type="hidden" name="module_id" id="lesson_module_id">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium">Title</label>
                            <input type="text" name="title" id="lesson_title" required
                                class="w-full rounded-xl border px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Type</label>
                            <select name="type" id="lesson_type" class="w-full rounded-xl border px-3 py-2" required>
                                <option value="text">Text</option>
                                <option value="video">Video</option>
                                <option value="pdf">PDF</option>
                                <option value="audio">Audio</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Duration (minutes)</label>
                            <input type="number" name="duration" id="lesson_duration" min="0"
                                class="w-full rounded-xl border px-3 py-2">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium">Content</label>
                            <textarea name="content" id="lesson_content" rows="6"
                                class="w-full rounded-xl border px-3 py-2"></textarea>
                            <p class="text-xs text-dark/60 mt-1">Isi HTML untuk <b>text</b>, atau URL/embeds untuk
                                <b>video/audio/pdf</b>.
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button id="btnSaveLesson" class="px-4 py-2 rounded-lg bg-brand text-white">Save Lesson</button>
                        <button id="btnResetLesson" type="button" class="px-4 py-2 rounded-lg border">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>