@csrf
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium">Title</label>
        <input type="text" name="title" value="{{ old('title', $course->title ?? '') }}" required
            class="w-full rounded-xl border border-soft px-3 py-2">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium">Description</label>
        <textarea name="description" rows="5" required
            class="w-full rounded-xl border border-soft px-3 py-2">{{ old('description', $course->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium">Category</label>
        <select name="category_id" class="w-full rounded-xl border border-soft px-3 py-2" required>
            <option value="">— Choose —</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(old('category_id', $course->category_id ?? null)==$cat->id)>{{
                $cat->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium">Duration (minutes)</label>
        <input type="number" name="duration" min="1" value="{{ old('duration', $course->duration ?? '') }}"
            class="w-full rounded-xl border border-soft px-3 py-2" required>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium">Thumbnail</label>
        <input type="file" name="thumbnail" accept="image/*" class="w-full rounded-xl border border-soft px-3 py-2">
        @if(!empty($course?->thumbnail_path))
        <p class="text-xs mt-1">Current: {{ $course->thumbnail_path }}</p>
        @endif
    </div>

    @if(Route::currentRouteNamed('courses.edit'))
    <div>
        <label class="block text-sm font-medium">Status</label>
        <select name="status" class="w-full rounded-xl border border-soft px-3 py-2">
            @foreach(['draft','published','archived'] as $st)
            <option value="{{ $st }}" @selected(old('status', $course->status ?? 'draft')===$st)>{{ ucfirst($st) }}
            </option>
            @endforeach
        </select>
    </div>
    @endif
</div>

<div class="mt-4 flex gap-2">
    <button class="px-4 py-2 rounded-lg bg-brand text-white">Save</button>
    @if(!Route::currentRouteNamed('courses.edit'))
    <button name="save_and_continue" value="1" class="px-4 py-2 rounded-lg border">Save & Continue</button>
    @endif
</div>