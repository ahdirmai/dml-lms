<x-app-layout :title="'Courses Management'">
    <x-slot name="header">
        Courses Management
    </x-slot>

    <div class="py-4">
        <div class="mx-auto">

            {{-- Alerts --}}
            @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
            @endif

            {{-- ===== Analytic Cards ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Total Courses</p>
                            <p class="mt-1 text-2xl font-bold text-dark">{{ number_format($stats['total'] ?? 0) }}</p>
                        </div>
                        <div class="rounded-xl p-2 bg-brand/10 text-brand border border-brand/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l9-5-9-5-9 5 9 5zM12 14v6m-5-3l5 3 5-3" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Published</p>
                            <p class="mt-1 text-2xl font-bold text-dark">{{ number_format($stats['published'] ?? 0) }}
                            </p>
                        </div>
                        <div class="rounded-xl p-2 bg-accent/10 text-accent border border-accent/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Draft</p>
                            <p class="mt-1 text-2xl font-bold text-dark">{{ number_format($stats['draft'] ?? 0) }}</p>
                        </div>
                        <div class="rounded-xl p-2 bg-gray-100 text-dark border border-soft">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- ===== Filters: Collapsible on mobile, full grid on md+ ===== --}}
            <x-ui.card class="mb-4">
                {{-- Mobile: collapsible --}}
                <details class="md:hidden">
                    <summary class="list-none cursor-pointer select-none flex items-center justify-between p-3 -m-3">
                        <span class="text-sm font-semibold text-dark">Filters</span>
                        <span class="text-xs text-dark/60">tap to expand</span>
                    </summary>
                    <div class="mt-3 pt-3 border-t border-soft">
                        <form method="GET" class="grid grid-cols-1 gap-3">
                            <x-ui.input name="q" :value="request('q')" placeholder="Search title or description"
                                class="w-full" />
                            <x-ui.select name="status">
                                <option value="">All Status</option>
                                <option value="published" @selected(request('status')==='published' )>Published</option>
                                <option value="draft" @selected(request('status')==='draft' )>Draft</option>
                                @if(($hasArchived ?? false) || request('status')==='archived')
                                <option value="archived" @selected(request('status')==='archived' )>Archived</option>
                                @endif
                            </x-ui.select>

                            <x-ui.select name="category_id">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string)request('category_id')===(string)$cat->
                                    id)>{{ $cat->name }}</option>
                                @endforeach
                            </x-ui.select>

                            <x-ui.select name="instructor_id">
                                <option value="">All Instructors</option>
                                @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}"
                                    @selected((string)request('instructor_id')===(string)$instructor->id)>{{
                                    $instructor->name }}</option>
                                @endforeach
                            </x-ui.select>

                            <x-ui.select name="sort">
                                <option value="date_desc" @selected(request('sort','date_desc')==='date_desc' )>Latest
                                </option>
                                <option value="date_asc" @selected(request('sort')==='date_asc' )>Oldest</option>
                                <option value="title_asc" @selected(request('sort')==='title_asc' )>Title (A-Z)</option>
                                <option value="title_desc" @selected(request('sort')==='title_desc' )>Title (Z-A)
                                </option>
                                <option value="status_asc" @selected(request('sort')==='status_asc' )>Status (Draft
                                    First)</option>
                                <option value="status_desc" @selected(request('sort')==='status_desc' )>Status
                                    (Published First)</option>
                            </x-ui.select>

                            <div class="flex items-center gap-2">
                                <x-ui.button type="submit" color="brand" class="w-full">Apply Filter</x-ui.button>
                                <a href="{{ route('admin.courses.index') }}"
                                    class="inline-flex items-center justify-center font-semibold rounded-lg border px-4 py-2 text-sm text-dark hover:bg-soft w-full">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </details>

                {{-- Desktop: full grid --}}
                <div class="hidden md:block">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
                        <x-ui.input name="q" :value="request('q')" placeholder="Search title or description"
                            class="w-full lg:col-span-2" />
                        <x-ui.select name="status">
                            <option value="">All Status</option>
                            <option value="published" @selected(request('status')==='published' )>Published</option>
                            <option value="draft" @selected(request('status')==='draft' )>Draft</option>
                            @if(($hasArchived ?? false) || request('status')==='archived')
                            <option value="archived" @selected(request('status')==='archived' )>Archived</option>
                            @endif
                        </x-ui.select>

                        <x-ui.select name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string)request('category_id')===(string)$cat->
                                id)>{{ $cat->name }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="instructor_id">
                            <option value="">All Instructors</option>
                            @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}"
                                @selected((string)request('instructor_id')===(string)$instructor->id)>{{
                                $instructor->name }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="sort">
                            <option value="date_desc" @selected(request('sort','date_desc')==='date_desc' )>Latest
                            </option>
                            <option value="date_asc" @selected(request('sort')==='date_asc' )>Oldest</option>
                            <option value="title_asc" @selected(request('sort')==='title_asc' )>Title (A-Z)</option>
                            <option value="title_desc" @selected(request('sort')==='title_desc' )>Title (Z-A)</option>
                            <option value="status_asc" @selected(request('sort')==='status_asc' )>Status (Draft First)
                            </option>
                            <option value="status_desc" @selected(request('sort')==='status_desc' )>Status (Published
                                First)</option>
                        </x-ui.select>

                        <div class="flex items-center gap-2 lg:col-span-2">
                            <x-ui.button type="submit" color="brand" class="w-full">Apply Filter</x-ui.button>
                            <a href="{{ route('admin.courses.index') }}"
                                class="inline-flex items-center justify-center font-semibold rounded-lg border px-4 py-2 text-sm text-dark hover:bg-soft w-full">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </x-ui.card>

            {{-- ===== Header List + Create ===== --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-2">
                <div class="text-sm text-dark/60 order-2 md:order-1">
                    Showing
                    <span class="font-semibold text-dark">{{ $courses->firstItem() ?? 0 }}</span>–
                    <span class="font-semibold text-dark">{{ $courses->lastItem() ?? 0 }}</span>
                    of <span class="font-semibold text-dark">{{ $courses->total() }}</span> results
                </div>

                <div class="order-1 md:order-2">
                    <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center justify-center font-semibold rounded-lg transition
                              focus:outline-none focus:ring-2 focus:ring-offset-2 px-4 py-2 text-sm bg-brand text-white
                              hover:brightness-95 focus:ring-brand w-full md:w-auto">
                        + Create
                    </a>
                </div>
            </div>

            {{-- ===== TABLE (Desktop/Tablet) ===== --}}
            <div class="bg-white shadow sm:rounded-lg hidden md:block">
                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full text-sm text-left divide-y divide-soft">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Title</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Category
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Tags</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Status
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Students
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Created By
                                </th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($courses as $c)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-dark">
                                    <div class="font-semibold">{{ $c->title }}</div>
                                    <div class="text-xs text-dark/60">{{ \Str::limit($c->description, 80) }}</div>
                                </td>

                                <td class="px-3 py-2">
                                    @if($c->categories->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($c->categories as $cat)
                                        <x-ui.badge color="dark">{{ $cat->name }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-xs text-dark/50">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2">
                                    @if($c->tags?->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($c->tags as $tag)
                                        <x-ui.badge color="accent">{{ $tag->name }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-xs text-dark/50">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2">
                                    @if($c->status === 'published')
                                    <x-ui.badge color="brand">Published</x-ui.badge>
                                    @elseif($c->status === 'archived')
                                    <x-ui.badge color="danger">Archived</x-ui.badge>
                                    @else
                                    <x-ui.badge color="gray">Draft</x-ui.badge>
                                    @endif
                                </td>

                                <td class="px-3 py-2">
                                    <span class="text-dark/70">{{ number_format($c->students_count ?? 0) }}</span>
                                </td>

                                <td class="px-3 py-2">
                                    <span class="text-dark/70">{{ $c->instructor->name ?? 'N/A' }}</span>
                                </td>

                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.courses.edit', $c->id) }}"
                                        class="inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-1.5 text-sm text-dark hover:bg-soft mr-1">
                                        Builder
                                    </a>
                                    <a href="{{ route('admin.courses.assign', $c->id) }}"
                                        class="inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-1.5 text-sm text-dark hover:bg-soft mr-1">
                                        Assign
                                    </a>
                                    <a href="{{ route('admin.courses.progress', $c->id) }}"
                                        class="inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-1.5 text-sm text-dark hover:bg-soft mr-1">
                                        Progress
                                    </a>
                                    <form action="{{ route('admin.courses.destroy', $c->id) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Delete this course?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center font-semibold rounded-lg px-3 py-1.5 text-sm bg-danger text-white hover:brightness-95">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">No courses found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== CARD LIST (Mobile) ===== --}}
            <div class="space-y-3 md:hidden">
                @forelse($courses as $c)
                <x-ui.card class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-dark text-base truncate">{{ $c->title }}</h3>
                            <p class="text-xs text-dark/60 line-clamp-2">{{ \Str::limit($c->description, 140) }}</p>
                        </div>
                        <div class="shrink-0">
                            @if($c->status === 'published')
                            <x-ui.badge color="brand">Published</x-ui.badge>
                            @elseif($c->status === 'archived')
                            <x-ui.badge color="danger">Archived</x-ui.badge>
                            @else
                            <x-ui.badge color="gray">Draft</x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-2 text-xs text-dark/70">
                        <div class="col-span-2">
                            <dt class="sr-only">Category</dt>
                            <dd>
                                <span class="text-dark/60">Category:</span>
                                @if($c->categories->isNotEmpty())
                                {{ \Illuminate\Support\Str::limit($c->categories->pluck('name')->join(', '), 60) }}
                                @else
                                —
                                @endif
                            </dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="sr-only">Tags</dt>
                            <dd>
                                <span class="text-dark/60">Tags:</span>
                                @if($c->tags?->isNotEmpty())
                                {{ \Illuminate\Support\Str::limit($c->tags->pluck('name')->join(', '), 60) }}
                                @else
                                —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="sr-only">Students</dt>
                            <dd><span class="text-dark/60">Students:</span> {{ number_format($c->students_count ?? 0) }}
                            </dd>
                        </div>
                        <div class="text-right">
                            <dt class="sr-only">Instructor</dt>
                            <dd><span class="text-dark/60">By:</span> {{ $c->instructor->name ?? 'N/A' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('admin.courses.edit', $c->id) }}"
                            class="flex-1 min-w-[7rem] inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-2 text-sm text-dark hover:bg-soft">
                            Builder
                        </a>
                        <a href="{{ route('admin.courses.assign', $c->id) }}"
                            class="flex-1 min-w-[7rem] inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-2 text-sm text-dark hover:bg-soft">
                            Assign
                        </a>
                        <a href="{{ route('admin.courses.progress', $c->id) }}"
                            class="flex-1 min-w-[7rem] inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-2 text-sm text-dark hover:bg-soft">
                            Progress
                        </a>
                        <form action="{{ route('admin.courses.destroy', $c->id) }}" method="POST"
                            onsubmit="return confirm('Delete this course?')" class="flex-1 min-w-[7rem]">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center font-semibold rounded-lg px-3 py-2 text-sm bg-danger text-white hover:brightness-95">
                                Delete
                            </button>
                        </form>
                    </div>
                </x-ui.card>
                @empty
                <x-ui.card class="p-6 text-center text-sm text-gray-500">No courses found</x-ui.card>
                @endforelse
            </div>

            {{-- ===== Pagination (visible on both modes) ===== --}}
            <div class="mt-4">
                {{ $courses->withQueryString()->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
