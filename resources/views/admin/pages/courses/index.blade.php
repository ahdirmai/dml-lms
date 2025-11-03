<x-app-layout :title="'Courses Management'">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dark leading-tight">Courses Management</h2>
    </x-slot>

    <div class="py-4">
        <div class="mx-auto">

            @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
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
                            {{-- icon sederhana --}}
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

            {{-- ===== Filter Card (terpisah dari table) ===== --}}
            <x-ui.card class="mb-4">
                <div class="">
                    <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        {{-- Search --}}
                        <x-ui.input name="q" :value="request('q')" placeholder="Search title or description"
                            class="w-full" />

                        {{-- Status --}}
                        <x-ui.select name="status">
                            <option value="">All Status</option>
                            <option value="published" @selected(request('status')==='published' )>Published</option>
                            <option value="draft" @selected(request('status')==='draft' )>Draft</option>
                            @if(($hasArchived ?? false) || request('status')==='archived')
                            <option value="archived" @selected(request('status')==='archived' )>Archived</option>
                            @endif
                        </x-ui.select>

                        {{-- Jenis Course = Category --}}
                        <x-ui.select name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string)request('category_id')===(string)$cat->id)>
                                {{ $cat->name }}
                            </option>
                            @endforeach
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
            </x-ui.card>

            {{-- ===== Table Section (tanpa form filter lagi) ===== --}}
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-4 border-b border-soft flex items-center justify-between">
                    <div class="text-sm text-dark/60">
                        Showing <span class="font-semibold text-dark">{{ $courses->firstItem() ?? 0 }}</span>–
                        <span class="font-semibold text-dark">{{ $courses->lastItem() ?? 0 }}</span>
                        of <span class="font-semibold text-dark">{{ $courses->total() }}</span> results
                    </div>

                    <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center justify-center font-semibold rounded-lg transition
                       focus:outline-none focus:ring-2 focus:ring-offset-2 px-4 py-2 text-sm bg-brand text-white
                       hover:brightness-95 focus:ring-brand">
                        + Create
                    </a>
                </div>

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
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Jumlah
                                    Siswa</th>
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

                                {{-- ✅ Category --}}
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

                                {{-- ✅ Tags --}}
                                <td class="px-3 py-2">
                                    @if($c->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($c->tags as $tag)
                                        <x-ui.badge color="accent">{{ $tag->name }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-xs text-dark/50">—</span>
                                    @endif
                                </td>

                                {{-- ✅ Status --}}
                                <td class="px-3 py-2">
                                    @if($c->status === 'published')
                                    <x-ui.badge color="brand">Published</x-ui.badge>
                                    @elseif($c->status === 'archived')
                                    <x-ui.badge color="danger">Archived</x-ui.badge>
                                    @else
                                    <x-ui.badge color="gray">Draft</x-ui.badge>
                                    @endif
                                </td>

                                {{-- ✅ Jumlah siswa --}}
                                <td class="px-3 py-2">
                                    <span class="text-dark/70">{{ $c->students_count ?? 0 }}</span>
                                </td>

                                {{-- ✅ JCreate By --}}
                                <td class="px-3 py-2">
                                    <span class="text-dark/70">xx</span>
                                </td>

                                {{-- ✅ Actions --}}
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('admin.courses.builder', $c) }}"
                                        class="inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-1.5 text-sm text-dark hover:bg-soft mr-1">Builder</a>
                                    <a href="{{ route('admin.courses.edit', $c) }}"
                                        class="inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-1.5 text-sm text-dark hover:bg-soft mr-1">Edit
                                    </a>
                                    <form action="{{ route('admin.courses.destroy', $c) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this course?')">@csrf @method('DELETE')<button
                                            class="inline-flex items-center justify-center font-semibold rounded-lg px-3 py-1.5 text-sm bg-danger text-white hover:brightness-95">Delete</button>
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

                    <div class="mt-4">
                        {{ $courses->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>