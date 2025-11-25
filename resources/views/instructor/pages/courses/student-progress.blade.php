{{-- resources/views/instructor/pages/courses/student-progress.blade.php --}}
<x-app-layout :title="'Student Progress — ' . $student->name">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-dark leading-tight">
                Student Progress — {{ $student->name }}
            </h2>
        </div>
    </x-slot>

    <a href="{{ route('instructor.courses.progress', $course->id) }}"
        class="inline-flex items-center justify-center font-semibold rounded-lg border px-4 py-2 text-sm text-dark hover:bg-soft">
        ← Back to Course Progress
    </a>

    <div class="">
        <div class="mx-auto max-w-7xl space-y-4">

            {{-- Student Info Card --}}
            <x-ui.card class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-dark mb-3">Student Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-semibold">Name:</span> {{ $student->name }}</div>
                            <div><span class="font-semibold">Email:</span> {{ $student->email }}</div>
                            <div><span class="font-semibold">Status:</span> 
                                @if($enrollment->status === 'active')
                                    <x-ui.badge color="brand">Active</x-ui.badge>
                                @elseif($enrollment->status === 'completed')
                                    <x-ui.badge color="accent">Completed</x-ui.badge>
                                @elseif($enrollment->status === 'assigned')
                                    <x-ui.badge color="gray">Assigned</x-ui.badge>
                                @else
                                    <x-ui.badge color="danger">{{ ucfirst($enrollment->status) }}</x-ui.badge>
                                @endif
                            </div>
                            <div><span class="font-semibold">Enrolled:</span> {{ $enrollment->enrolled_at?->format('d M Y') ?? '-' }}</div>
                            @if($enrollment->completed_at)
                                <div><span class="font-semibold">Completed:</span> {{ $enrollment->completed_at->format('d M Y') }}</div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-dark mb-3">Course Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-semibold">Course:</span> {{ $course->title }}</div>
                            @if($course->using_due_date && $enrollment->dueDate)
                                <div><span class="font-semibold">Start Date:</span> {{ \Carbon\Carbon::parse($enrollment->dueDate->start_date)->format('d M Y') }}</div>
                                <div><span class="font-semibold">End Date:</span> {{ \Carbon\Carbon::parse($enrollment->dueDate->end_date)->format('d M Y') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Quiz Attempts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Pretest Attempts --}}
                <x-ui.card class="p-0 overflow-hidden">
                    <div class="p-4 border-b border-soft">
                        <h3 class="font-bold text-lg text-dark">Pretest Attempts</h3>
                    </div>
                    <div class="p-4">
                        @if($pretestAttempts && count($pretestAttempts) > 0)
                            <div class="space-y-2">
                                @foreach($pretestAttempts as $attempt)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <div class="text-sm font-semibold">Attempt #{{ $attempt->attempt_no }}</div>
                                            <div class="text-xs text-dark/60">{{ $attempt->created_at->format('d M Y, H:i') }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $attempt->score }}%
                                            </div>
                                            <div class="text-xs">
                                                @if($attempt->passed)
                                                    <x-ui.badge color="green">Passed</x-ui.badge>
                                                @else
                                                    <x-ui.badge color="danger">Failed</x-ui.badge>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-dark/60 py-4">No pretest attempts yet.</div>
                        @endif
                    </div>
                </x-ui.card>

                {{-- Posttest Attempts --}}
                <x-ui.card class="p-0 overflow-hidden">
                    <div class="p-4 border-b border-soft">
                        <h3 class="font-bold text-lg text-dark">Posttest Attempts</h3>
                    </div>
                    <div class="p-4">
                        @if($posttestAttempts && count($posttestAttempts) > 0)
                            <div class="space-y-2">
                                @foreach($posttestAttempts as $attempt)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <div class="text-sm font-semibold">Attempt #{{ $attempt->attempt_no }}</div>
                                            <div class="text-xs text-dark/60">{{ $attempt->created_at->format('d M Y, H:i') }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $attempt->score }}%
                                            </div>
                                            <div class="text-xs">
                                                @if($attempt->passed)
                                                    <x-ui.badge color="green">Passed</x-ui.badge>
                                                @else
                                                    <x-ui.badge color="danger">Failed</x-ui.badge>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-dark/60 py-4">No posttest attempts yet.</div>
                        @endif
                    </div>
                </x-ui.card>
            </div>

            {{-- Lesson Progress --}}
            <x-ui.card class="p-0 overflow-hidden">
                <div class="p-4 border-b border-soft">
                    <h3 class="font-bold text-lg text-dark">Lesson Progress</h3>
                </div>
                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full text-sm text-left divide-y divide-soft">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Module</th>
                                <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Lesson</th>
                                <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Duration</th>
                                <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Last Activity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($lessonProgress as $lp)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-dark/70">{{ $lp->lesson->module->title ?? '-' }}</td>
                                    <td class="px-3 py-2 text-dark font-medium">{{ $lp->lesson->title }}</td>
                                    <td class="px-3 py-2">
                                        @if($lp->status === 'completed')
                                            <x-ui.badge color="green">Completed</x-ui.badge>
                                        @elseif($lp->status === 'in_progress')
                                            <x-ui.badge color="brand">In Progress</x-ui.badge>
                                        @else
                                            <x-ui.badge color="gray">Not Started</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-dark/70">{{ convert_seconds_to_duration($lp->duration_seconds ?? 0) }}</td>
                                    <td class="px-3 py-2 text-dark/60">{{ $lp->last_activity_at?->diffForHumans() ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-dark/60">No lesson progress yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            {{-- Activity Log --}}
            <x-ui.card class="p-0 overflow-hidden">
                <div class="p-4 border-b border-soft">
                    <h3 class="font-bold text-lg text-dark">Activity Log (Last 50)</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @forelse($activityLogs as $log)
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-brand/10 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-dark">{{ ucfirst(str_replace('_', ' ', $log->activity_type)) }}</div>
                                    <div class="text-xs text-dark/70 mt-1">{{ $log->description }}</div>
                                    <div class="text-xs text-dark/50 mt-1">{{ $log->created_at->format('d M Y, H:i') }} • {{ $log->ip_address }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-dark/60 py-4">No activity logs yet.</div>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
