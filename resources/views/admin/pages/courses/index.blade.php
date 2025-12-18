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
                    <a href="{{ route('admin.courses.export.scores', request()->query()) }}" class="inline-flex items-center justify-center font-semibold rounded-lg transition
    focus:outline-none focus:ring-2 focus:ring-offset-2 px-4 py-2 text-sm bg-white border border-gray-300 text-gray-700
    hover:bg-gray-50 focus:ring-brand w-full md:w-auto ml-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export Scores
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
                                    <div class="flex items-center gap-3">
                                        <div class="shrink-0 w-16 h-10 bg-gray-100 rounded overflow-hidden">
                                            @if($c->thumbnail_path)
                                                <img src="{{ Storage::url($c->thumbnail_path) }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-semibold">{{ $c->title }}</div>
                                            <div class="text-xs text-dark/60">{{ \Str::limit($c->description, 80) }}</div>
                                        </div>
                                    </div>
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
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="menu-button" aria-expanded="true" aria-haspopup="true">
                                            @if($c->status === 'published')
                                                <span class="text-green-600 font-semibold">Published</span>
                                            @elseif($c->status === 'archived')
                                                <span class="text-red-600 font-semibold">Archived</span>
                                            @else
                                                <span class="text-gray-600 font-semibold">Draft</span>
                                            @endif
                                            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1" style="display: none;">
                                            <div class="py-1" role="none">
                                                @if($c->status !== 'published')
                                                    <form method="POST" action="{{ route('admin.courses.status.update', $c->id) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="published">
                                                        <button type="submit" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1" id="menu-item-0">
                                                            Set as Published
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.courses.status.update', $c->id) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="draft">
                                                        <button type="submit" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1" id="menu-item-1">
                                                            Set as Draft
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-2">
                                    <span class="text-dark/70">{{ number_format($c->enrollments_count ?? 0) }}</span>
                                </td>

                                <td class="px-3 py-2">
                                    <span class="text-dark/70">{{ $c->instructor->name ?? 'N/A' }}</span>
                                </td>

                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <x-dropdown align="right" width="48">
                                        <x-slot name="trigger">
                                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                                <div>Actions</div>
                                                <div class="ml-1">
                                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </button>
                                        </x-slot>

                                        <x-slot name="content">
                                            <x-dropdown-link :href="route('admin.courses.edit', $c->id)">
                                                Builder
                                            </x-dropdown-link>
                                            <x-dropdown-link :href="route('admin.courses.assign', $c->id)">
                                                Assign Students
                                            </x-dropdown-link>
                                            <x-dropdown-link :href="route('admin.courses.progress', $c->id)">
                                                Check Progress
                                            </x-dropdown-link>
                                            <x-dropdown-link :href="route('admin.courses.export.single', $c->id)">
                                                Export Scores
                                            </x-dropdown-link>
                                            <div class="border-t border-gray-100"></div>
                                            <form method="POST" action="{{ route('admin.courses.destroy', $c->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <x-dropdown-link :href="route('admin.courses.destroy', $c->id)"
                                                        onclick="event.preventDefault(); if(confirm('Are you sure?')) this.closest('form').submit();" class="text-red-600">
                                                    Delete
                                                </x-dropdown-link>
                                            </form>
                                        </x-slot>
                                    </x-dropdown>
                                </td>
                            </tr>
                            @empty
                            <tr>
                            <tr>
                                <td colspan="7" class="px-3 py-12 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        <p class="text-lg font-medium text-gray-900">No courses found</p>
                                        <p class="text-gray-500">Get started by creating a new course.</p>
                                        <a href="{{ route('admin.courses.create') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand">
                                            Create Course
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== CARD LIST (Mobile) ===== --}}
            <div class="space-y-3 md:hidden">
                @forelse($courses as $c)
                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-20 h-20 bg-gray-100 rounded overflow-hidden">
                            @if($c->thumbnail_path)
                                <img src="{{ Storage::url($c->thumbnail_path) }}" alt="" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-dark text-base truncate">{{ $c->title }}</h3>
                                <div class="shrink-0">
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-2 py-1 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="menu-button-mobile-{{ $c->id }}" aria-expanded="true" aria-haspopup="true">
                                            @if($c->status === 'published')
                                                <span class="text-green-600 font-semibold">Published</span>
                                            @elseif($c->status === 'archived')
                                                <span class="text-red-600 font-semibold">Archived</span>
                                            @else
                                                <span class="text-gray-600 font-semibold">Draft</span>
                                            @endif
                                            <svg class="-mr-1 ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="menu-button-mobile-{{ $c->id }}" tabindex="-1" style="display: none;">
                                            <div class="py-1" role="none">
                                                @if($c->status !== 'published')
                                                    <form method="POST" action="{{ route('admin.courses.status.update', $c->id) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="published">
                                                        <button type="submit" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1">
                                                            Set as Published
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.courses.status.update', $c->id) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="draft">
                                                        <button type="submit" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1">
                                                            Set as Draft
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-dark/60 line-clamp-2 mt-1">{{ \Str::limit($c->description, 140) }}</p>
                            
                            <dl class="mt-2 grid grid-cols-2 gap-x-2 gap-y-1 text-xs text-dark/70">
                                <div class="col-span-2">
                                    <span class="text-dark/60">Category:</span>
                                    @if($c->categories->isNotEmpty())
                                    {{ \Illuminate\Support\Str::limit($c->categories->pluck('name')->join(', '), 60) }}
                                    @else
                                    —
                                    @endif
                                </div>
                                <div>
                                    <span class="text-dark/60">Students:</span> {{ number_format($c->enrollments_count ?? 0) }}
                                </div>
                                <div class="text-right">
                                    <span class="text-dark/60">By:</span> {{ $c->instructor->name ?? 'N/A' }}
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-soft flex justify-end gap-2">
                         <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150 w-full justify-center">
                                    Actions
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('admin.courses.edit', $c->id)">
                                    Builder
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.courses.assign', $c->id)">
                                    Assign Students
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.courses.progress', $c->id)">
                                    Check Progress
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.courses.export.single', $c->id)">
                                    Export Scores
                                </x-dropdown-link>
                                <div class="border-t border-gray-100"></div>
                                <form method="POST" action="{{ route('admin.courses.destroy', $c->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-dropdown-link :href="route('admin.courses.destroy', $c->id)"
                                            onclick="event.preventDefault(); if(confirm('Are you sure?')) this.closest('form').submit();" class="text-red-600">
                                        Delete
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
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
