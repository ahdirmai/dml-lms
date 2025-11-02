<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Permissions</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                    <form method="GET" class="flex items-center gap-2">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Search permission"
                            class="w-64 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <button class="px-3 py-2 rounded-md bg-indigo-600 text-white">Filter</button>
                    </form>

                    <a href="{{ route('admin.permissions.create') }}"
                        class="px-3 py-2 rounded-md bg-green-600 text-white">+ Create</a>
                </div>

                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Permission
                                </th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($permissions as $p)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $p->name }}</td>
                                <td class="px-3 py-2 text-sm text-right">
                                    <a href="{{ route('admin.permissions.edit', $p) }}"
                                        class="px-3 py-1 rounded border">Edit</a>
                                    <form action="{{ route('admin.permissions.destroy', $p) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Delete this permission?')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1 rounded bg-red-600 text-white">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>