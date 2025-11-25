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
