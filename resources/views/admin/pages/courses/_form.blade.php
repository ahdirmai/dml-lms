<form method="POST" action="{{ $action ?? '' }}" enctype="multipart/form-data" novalidate>
    @csrf
    @if(($method ?? 'POST') !== 'POST')
    @method($method)
    @endif

    {{-- Top-level error summary --}}
    @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50 p-3 text-red-700 text-sm">
        <p class="font-medium mb-1">Please fix the following:</p>
        <ul class="list-disc ml-5 space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium {{ $errors->has('title') ? 'text-red-700' : '' }}">Title</label>
            <input type="text" name="title" value="{{ old('title', $course->title ?? '') }}" required class="w-full rounded-xl border px-3 py-2
                       {{ $errors->has('title') ? 'border-red-400 focus:ring-red-400' : 'border-soft' }}">
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium {{ $errors->has('subtitle') ? 'text-red-700' : '' }}">Sub
                Title</label>
            <input type="text" name="subtitle" value="{{ old('subtitle', $course->subtitle ?? '') }}" class="w-full rounded-xl border px-3 py-2
                       {{ $errors->has('subtitle') ? 'border-red-400 focus:ring-red-400' : 'border-soft' }}">
            @error('subtitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label
                class="block text-sm font-medium {{ $errors->has('description') ? 'text-red-700' : '' }}">Description</label>
            <textarea name="description" rows="5" required
                class="w-full rounded-xl border px-3 py-2
                       {{ $errors->has('description') ? 'border-red-400 focus:ring-red-400' : 'border-soft' }}">{{ old('description', $course->description ?? '') }}</textarea>
            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label
                class="block text-sm font-medium {{ $errors->has('category_id') ? 'text-red-700' : '' }}">Category</label>
            <select name="category_id" required class="w-full rounded-xl border px-3 py-2
                       {{ $errors->has('category_id') ? 'border-red-400 focus:ring-red-400' : 'border-soft' }}">
                <option value="">— Choose —</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $course->category_id ?? null) == $cat->id)>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>
            @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Optional: your controller doesn’t validate level, but the UI has it. Keep it if used elsewhere. --}}
        <div>
            <label class="block text-sm font-medium">Level</label>
            <select name="level" class="w-full rounded-xl border border-soft px-3 py-2">
                @foreach(['beginner','intermediate','advanced'] as $lvl)
                <option value="{{ $lvl }}" @selected(old('level', $course->level ?? 'beginner') === $lvl)>{{
                    ucfirst($lvl) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label
                class="block text-sm font-medium {{ $errors->has('instructor_id') ? 'text-red-700' : '' }}">Instructor</label>
            <select name="instructor_id" required
                class="w-full rounded-xl border px-3 py-2
                               {{ $errors->has('instructor_id') ? 'border-red-400 focus:ring-red-400' : 'border-soft' }}">
                <option value="">— Choose —</option>
                @foreach($instructors as $ins)
                <option value="{{ $ins->id }}" @selected(old('instructor_id', $course->instructor_id ?? null) ==
                    $ins->id)>
                    {{ $ins->name }}
                </option>
                @endforeach
            </select>
            @error('instructor_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        {{-- Duration (required by controller) --}}
        {{-- <div>
            <label class="block text-sm font-medium {{ $errors->has('duration') ? 'text-red-700' : '' }}">Duration
                (minutes)</label>
            <input type="number" name="duration" min="1" value="{{ old('duration', $course->duration ?? '') }}" required
                class="w-full rounded-xl border px-3 py-2
                       {{ $errors->has('duration') ? 'border-red-400 focus:ring-red-400' : 'border-soft' }}">
            @error('duration') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div> --}}

        <div class="sm:col-span-2">
            <label
                class="block text-sm font-medium {{ $errors->has('thumbnail') ? 'text-red-700' : '' }}">Thumbnail</label>
            <input type="file" name="thumbnail" accept="image/*" class="w-full rounded-xl border px-3 py-2
                       {{ $errors->has('thumbnail') ? 'border-red-400 focus:ring-red-400' : 'border-soft' }}">
            @error('thumbnail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @if(!empty($course?->thumbnail_path))
            <p class="text-xs mt-1">Current: {{ $course->thumbnail_path }}</p>
            @endif
        </div>

        @if(Route::currentRouteNamed('courses.edit'))
        <div>
            <label class="block text-sm font-medium {{ $errors->has('status') ? 'text-red-700' : '' }}">Status</label>
            <select name="status" class="w-full rounded-xl border px-3 py-2
                           {{ $errors->has('status') ? 'border-red-400 focus:ring-red-400' : 'border-soft' }}">
                @foreach(['draft','published','archived'] as $st)
                <option value="{{ $st }}" @selected(old('status', $course->status ?? 'draft') === $st)>{{ ucfirst($st)
                    }}</option>
                @endforeach
            </select>
            @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        @endif
    </div>

    <div class="mt-4 flex gap-2">
        <button class="px-4 py-2 rounded-lg bg-brand text-white" type="submit">Save</button>
        @if(!Route::currentRouteNamed('courses.edit'))
        <button name="save_and_continue" value="1" class="px-4 py-2 rounded-lg border" type="submit">Save &
            Continue</button>
        @endif
    </div>
</form>