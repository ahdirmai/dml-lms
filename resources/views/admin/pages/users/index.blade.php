{{-- resources/views/admin/pages/users/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Users Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
            @endif
            @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                {{ session('error') }}
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                    <form method="GET" class="flex items-center gap-2">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Search name/email"
                            class="w-64 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <select name="role"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">All Roles</option>
                            @foreach($roles as $r)
                            <option value="{{ $r }}" @selected($role===$r)>{{ Str::headline($r) }}</option>
                            @endforeach
                        </select>
                        <button class="px-3 py-2 rounded-md bg-indigo-600 text-white">Filter</button>
                    </form>

                    <a href="{{ route('admin.users.create') }}" class="px-3 py-2 rounded-md bg-green-600 text-white">+
                        Create</a>
                </div>

                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($users as $u)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $u->id }}</td>
                                <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $u->name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $u->email }}</td>
                                <td class="px-3 py-2 text-sm">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($u->roles as $r)
                                        <span
                                            class="inline-flex px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border dark:border-gray-600">
                                            {{ Str::headline($r->name) }}
                                        </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-sm">
                                    @if($u->active_role)
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded bg-indigo-100 text-indigo-800">
                                        {{ Str::headline($u->active_role) }}
                                    </span>
                                    @else
                                    <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-right">
                                    <a href="{{ route('admin.users.edit', $u) }}"
                                        class="px-3 py-1 rounded border text-gray-700 dark:text-gray-200">Edit</a>
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1 rounded bg-red-600 text-white">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">No data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>