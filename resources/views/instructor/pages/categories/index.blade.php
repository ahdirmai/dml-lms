<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dark leading-tight">Categories</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto">

            @if (session('success')) <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error')) <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert> @endif

            <div class="bg-white shadow sm:rounded-lg mb-3">
                <div
                    class="p-4 border-b border-soft flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <x-ui.input name="q" value="{{ $q }}" placeholder="Search name/slug" class="w-64" />
                        <x-ui.button type="submit" variant="primary">Filter</x-ui.button>
                        @if(request()->filled('q'))
                        <x-ui.button as="a" href="{{ route('instructor.categories.index') }}" variant="subtle">Reset
                        </x-ui.button>
                        @endif
                    </form>

                    <x-ui.button as="a" href="{{ route('instructor.categories.create') }}" variant="primary">+ Create
                    </x-ui.button>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">


                <div class="p-4 overflow-x-auto">
                    <x-ui.table>
                        <x-ui.thead>
                            <x-ui.th>Name</x-ui.th>
                            <x-ui.th>Slug</x-ui.th>
                            <x-ui.th>Description</x-ui.th>
                            <x-ui.th align="right">Action</x-ui.th>
                        </x-ui.thead>
                        <x-ui.tbody>
                            @forelse($categories as $c)
                            <x-ui.tr>
                                <x-ui.td><span class="font-medium text-dark">{{ $c->name }}</span></x-ui.td>
                                <x-ui.td><code class="text-xs text-dark/60">{{ $c->slug }}</code></x-ui.td>
                                <x-ui.td>{{ Str::limit($c->description, 80) }}</x-ui.td>
                                <x-ui.td align="right">
                                    <div class="flex justify-end gap-2">
                                        <x-ui.button as="a" href="{{ route('instructor.categories.edit',$c) }}"
                                            size="sm" variant="outline">Edit</x-ui.button>
                                        <form action="{{ route('instructor.categories.destroy',$c) }}" method="POST"
                                            onsubmit="return confirm('Delete this category?')">
                                            @csrf @method('DELETE')
                                            <x-ui.button type="submit" size="sm" variant="danger">Delete</x-ui.button>
                                        </form>
                                    </div>
                                </x-ui.td>
                            </x-ui.tr>
                            @empty
                            <tr>
                                <td colspan="4">
                                    <x-ui.empty-state title="No categories" />
                                </td>
                            </tr>
                            @endforelse
                        </x-ui.tbody>
                    </x-ui.table>

                    <div class="mt-4">{{ $categories->links() }}</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
