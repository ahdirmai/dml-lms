<x-app-layout :title="'User Activity Logs'">
    <x-slot name="header">
        User Activity Logs
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

            {{-- Filters --}}
            <div class="bg-white p-4 rounded-lg shadow mb-4">
                <form action="{{ route('admin.user-activity.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <x-text-input id="search" name="search" type="text" class="w-full text-sm" placeholder="User, Desc..." :value="request('search')" />
                    </div>

                    {{-- Type --}}
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Activity Type</label>
                        <select id="type" name="type" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">All Types</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <x-text-input id="date_from" name="date_from" type="date" class="w-full text-sm" :value="request('date_from')" />
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <div class="flex gap-2">
                            <x-text-input id="date_to" name="date_to" type="date" class="w-full text-sm" :value="request('date_to')" />
                            
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Filter
                            </button>
                            
                            @if(request()->anyFilled(['search', 'type', 'date_from', 'date_to']))
                            <a href="{{ route('admin.user-activity.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150" title="Reset Filters">
                                <span class="sr-only">Reset</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- ===== Header List ===== --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-2">
                <div class="text-sm text-dark/60 order-2 md:order-1">
                    Showing
                    <span class="font-semibold text-dark">{{ $activities->firstItem() ?? 0 }}</span>–
                    <span class="font-semibold text-dark">{{ $activities->lastItem() ?? 0 }}</span>
                    of <span class="font-semibold text-dark">{{ $activities->total() }}</span> results
                </div>
            </div>

            {{-- ===== TABLE (Desktop/Tablet) ===== --}}
            <div class="bg-white shadow sm:rounded-lg hidden md:block">
                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full text-sm text-left divide-y divide-soft">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">User</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Role</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Activity Type</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">IP Address</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Date/Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($activities as $activity)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-dark">
                                    <div class="font-semibold">{{ $activity->user->name ?? 'Unknown User' }}</div>
                                    <div class="text-xs text-dark/60">{{ $activity->user->email ?? '' }}</div>
                                </td>
                                <td class="px-3 py-2 text-dark">
                                    @if($activity->user)
                                        @foreach($activity->user->getRoleNames() as $role)
                                            <x-ui.badge color="brand">{{ $role }}</x-ui.badge>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-dark/50">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-dark">
                                    <span class="font-medium">{{ $activity->activity_type }}</span>
                                </td>
                                <td class="px-3 py-2 text-dark">
                                    {{ $activity->description }}
                                </td>
                                <td class="px-3 py-2 text-dark font-mono text-xs">
                                    {{ $activity->ip_address }}
                                </td>
                                <td class="px-3 py-2 text-dark text-xs">
                                    {{ $activity->created_at->format('d M Y H:i:s') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">No activity logs found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== CARD LIST (Mobile) ===== --}}
            <div class="space-y-3 md:hidden">
                @forelse($activities as $activity)
                <x-ui.card class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-dark text-base truncate">{{ $activity->user->name ?? 'Unknown User' }}</h3>
                            <p class="text-xs text-dark/60">{{ $activity->user->email ?? '' }}</p>
                        </div>
                        <div class="shrink-0">
                            <span class="text-xs font-medium bg-gray-100 text-gray-800 px-2 py-0.5 rounded">{{ $activity->activity_type }}</span>
                        </div>
                    </div>

                    <dl class="mt-3 grid grid-cols-1 gap-2 text-xs text-dark/70">
                        <div>
                            <dt class="sr-only">Description</dt>
                            <dd>{{ $activity->description }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="sr-only">IP Address</dt>
                            <dd class="font-mono">{{ $activity->ip_address }}</dd>
                            <dt class="sr-only">Date</dt>
                            <dd>{{ $activity->created_at->format('d M Y H:i') }}</dd>
                        </div>
                    </dl>
                </x-ui.card>
                @empty
                <x-ui.card class="p-6 text-center text-sm text-gray-500">No activity logs found</x-ui.card>
                @endforelse
            </div>

            {{-- ===== Pagination ===== --}}
            <div class="mt-4">
                {{ $activities->withQueryString()->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
