{{-- resources/views/admin/pages/permissions/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dark leading-tight">
            Permission Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto">

            {{-- Alerts --}}
            @if (session('success'))
            <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif
            <div class="bg-white shadow sm:rounded-lg mb-3">
                {{-- Filter & Action Bar --}}
                <div
                    class="p-4 border-b border-soft flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <x-ui.input type="text" name="q" value="{{ $q }}" placeholder="Search permission"
                            class="w-64" />
                        <x-ui.button type="submit" variant="primary">Filter</x-ui.button>
                        @if(request()->filled('q'))
                        <x-ui.button as="a" href="{{ route('admin.permissions.index') }}" variant="subtle">Reset
                        </x-ui.button>
                        @endif
                    </form>

                    <x-ui.button as="a" href="{{ route('admin.permissions.create') }}" variant="primary">
                        + Create
                    </x-ui.button>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">


                {{-- Table --}}
                <div class="p-4 overflow-x-auto">
                    <x-ui.table>
                        <x-ui.thead>
                            <x-ui.th>Permission</x-ui.th>
                            <x-ui.th align="right">Action</x-ui.th>
                        </x-ui.thead>

                        <x-ui.tbody>
                            @forelse($permissions as $p)
                            <x-ui.tr>
                                <x-ui.td>
                                    <span class="font-medium text-dark">{{ $p->name }}</span>
                                </x-ui.td>

                                <x-ui.td align="right">
                                    <div class="flex justify-end gap-2">
                                        <x-ui.button as="a" href="{{ route('admin.permissions.edit', $p) }}" size="sm"
                                            variant="outline">
                                            Edit
                                        </x-ui.button>

                                        <form action="{{ route('admin.permissions.destroy', $p) }}" method="POST"
                                            onsubmit="return confirm('Delete this permission?')">
                                            @csrf @method('DELETE')
                                            <x-ui.button type="submit" size="sm" variant="danger">Delete</x-ui.button>
                                        </form>
                                    </div>
                                </x-ui.td>
                            </x-ui.tr>
                            @empty
                            <tr>
                                <td colspan="2">
                                    <x-ui.empty-state title="No permissions found"
                                        subtitle="Tambahkan permission baru untuk mengatur akses sistem.">
                                        <x-ui.button as="a" href="{{ route('admin.permissions.create') }}"
                                            variant="primary">
                                            Create Permission
                                        </x-ui.button>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                            @endforelse
                        </x-ui.tbody>
                    </x-ui.table>

                    <div class="mt-4">
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>