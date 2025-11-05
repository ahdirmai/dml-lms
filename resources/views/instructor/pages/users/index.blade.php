{{-- resources/views/admin/pages/users/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dark leading-tight">Users Management</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto">

            {{-- Alerts --}}
            @if (session('success'))
            <x-ui.alert variant="success" class="mb-4">
                {{ session('success') }}
            </x-ui.alert>
            @endif
            @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">
                {{ session('error') }}
            </x-ui.alert>
            @endif
            <div class="bg-white shadow sm:rounded-lg mb-3">
                {{-- Toolbar / Filters --}}
                <div class="p-4 border-b border-soft flex flex-col md:flex-row md:items-center gap-3 justify-between">
                    <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <x-ui.input type="text" name="q" value="{{ $q }}" placeholder="Search name/email"
                            class="w-64" />
                        <x-ui.select name="role">
                            <option value="">All Roles</option>
                            @foreach($roles as $r)
                            <option value="{{ $r }}" @selected($role===$r)>{{ Str::headline($r) }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.button type="submit" variant="primary">Filter</x-ui.button>
                        @if(request()->hasAny(['q','role']) && (filled($q) || filled($role)))
                        <x-ui.button as="a" href="{{ route('admin.users.index') }}" variant="subtle">Reset</x-ui.button>
                        @endif
                    </form>

                    <x-ui.button as="a" href="{{ route('admin.users.create') }}" variant="primary">
                        + Create
                    </x-ui.button>
                </div>
            </div>
            <div class="bg-white shadow sm:rounded-lg">


                {{-- Table --}}
                <div class="p-4 overflow-x-auto">
                    <x-ui.table>
                        <x-ui.thead>
                            <x-ui.th>ID</x-ui.th>
                            <x-ui.th>Name</x-ui.th>
                            <x-ui.th>Email</x-ui.th>
                            <x-ui.th>Roles</x-ui.th>
                            <x-ui.th>Active</x-ui.th>
                            <x-ui.th align="right"></x-ui.th>
                        </x-ui.thead>

                        <x-ui.tbody>
                            @forelse($users as $u)
                            <x-ui.tr>
                                <x-ui.td>{{ $u->id }}</x-ui.td>

                                <x-ui.td>
                                    <div class="text-dark">{{ $u->name }}</div>
                                </x-ui.td>

                                <x-ui.td>
                                    <div class="text-sm text-dark/60">{{ $u->email }}</div>
                                </x-ui.td>

                                <x-ui.td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($u->roles as $r)
                                        <x-ui.badge color="gray">{{ Str::headline($r->name) }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                </x-ui.td>

                                <x-ui.td>
                                    @if($u->active_role)
                                    <x-ui.badge color="brand">{{ Str::headline($u->active_role) }}</x-ui.badge>
                                    @else
                                    <span class="text-xs text-dark/50">—</span>
                                    @endif
                                </x-ui.td>

                                <x-ui.td align="right">
                                    <x-ui.button as="a" href="{{ route('admin.users.edit', $u) }}" size="sm"
                                        variant="outline" class="mr-1">
                                        Edit
                                    </x-ui.button>

                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <x-ui.button type="submit" size="sm" variant="danger">
                                            Delete
                                        </x-ui.button>
                                    </form>
                                </x-ui.td>
                            </x-ui.tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <x-ui.empty-state title="No users found"
                                        subtitle="Coba ubah filter pencarian atau tambah user baru.">
                                        <x-ui.button as="a" href="{{ route('admin.users.create') }}" variant="primary">
                                            Create User</x-ui.button>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                            @endforelse
                        </x-ui.tbody>
                    </x-ui.table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>