{{-- resources/views/admin/pages/roles/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dark leading-tight">
            Role Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alerts --}}
            @if (session('success'))
            <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif

            <div class="bg-white shadow sm:rounded-lg">
                {{-- Filters / Actions --}}
                <div
                    class="p-4 border-b border-soft flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <x-ui.input type="text" name="q" value="{{ $q }}" placeholder="Search role" class="w-64" />
                        <x-ui.button type="submit" variant="primary">Filter</x-ui.button>
                        @if(request()->filled('q'))
                        <x-ui.button as="a" href="{{ route('admin.roles.index') }}" variant="subtle">Reset</x-ui.button>
                        @endif
                    </form>

                    <x-ui.button as="a" href="{{ route('admin.roles.create') }}" variant="primary">
                        + Create
                    </x-ui.button>
                </div>

                {{-- Table --}}
                <div class="p-4 overflow-x-auto">
                    <x-ui.table>
                        <x-ui.thead>
                            <x-ui.th>Name</x-ui.th>
                            <x-ui.th>Guard</x-ui.th>
                            <x-ui.th>Permissions</x-ui.th>
                            <x-ui.th align="right">Action</x-ui.th>
                        </x-ui.thead>

                        <x-ui.tbody>
                            @forelse($roles as $r)
                            <x-ui.tr>
                                <x-ui.td>
                                    <span class="font-medium text-dark">{{ $r->name }}</span>
                                </x-ui.td>
                                <x-ui.td>
                                    <span class="text-sm text-dark/70">{{ $r->guard_name }}</span>
                                </x-ui.td>
                                <x-ui.td>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($r->permissions as $p)
                                        <x-ui.badge color="gray">{{ $p->name }}</x-ui.badge>
                                        @empty
                                        <span class="text-xs text-dark/50">—</span>
                                        @endforelse
                                    </div>
                                </x-ui.td>
                                <x-ui.td align="right">
                                    <div class="flex justify-end gap-2">
                                        <x-ui.button as="a" href="{{ route('admin.roles.edit', $r) }}" size="sm"
                                            variant="outline">
                                            Edit
                                        </x-ui.button>

                                        <form action="{{ route('admin.roles.destroy', $r) }}" method="POST"
                                            onsubmit="return confirm('Delete this role?')">
                                            @csrf @method('DELETE')
                                            <x-ui.button type="submit" size="sm" variant="danger">Delete</x-ui.button>
                                        </form>
                                    </div>
                                </x-ui.td>
                            </x-ui.tr>
                            @empty
                            <tr>
                                <td colspan="4">
                                    <x-ui.empty-state title="No roles found"
                                        subtitle="Buat role baru untuk mengelola akses.">
                                        <x-ui.button as="a" href="{{ route('admin.roles.create') }}" variant="primary">
                                            Create Role
                                        </x-ui.button>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                            @endforelse
                        </x-ui.tbody>
                    </x-ui.table>

                    <div class="mt-4">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>