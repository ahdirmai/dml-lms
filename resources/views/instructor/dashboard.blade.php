<x-app-layout :title="'Instructor Dashboard'">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dark leading-tight">
            Instructor Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            
            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <x-ui.card class="p-4 border-l-4 border-brand">
                    <div class="text-sm text-dark/60 uppercase font-bold">Total Courses</div>
                    <div class="mt-2 text-3xl font-bold text-dark">{{ number_format($totalCourses) }}</div>
                    <div class="text-xs text-dark/50 mt-1">{{ $publishedCourses }} Published</div>
                </x-ui.card>

                <x-ui.card class="p-4 border-l-4 border-accent">
                    <div class="text-sm text-dark/60 uppercase font-bold">Total Students</div>
                    <div class="mt-2 text-3xl font-bold text-dark">{{ number_format($totalStudents) }}</div>
                    <div class="text-xs text-dark/50 mt-1">Unique Enrollments</div>
                </x-ui.card>

                <x-ui.card class="p-4 border-l-4 border-green-500">
                    <div class="text-sm text-dark/60 uppercase font-bold">Completions</div>
                    <div class="mt-2 text-3xl font-bold text-dark">{{ number_format($totalCompletions) }}</div>
                    <div class="text-xs text-dark/50 mt-1">Finished Courses</div>
                </x-ui.card>

                <x-ui.card class="p-4 flex flex-col justify-center items-center bg-brand/5 border border-brand/20">
                    <a href="{{ route('instructor.courses.create') }}" class="w-full h-full flex flex-col items-center justify-center text-brand hover:text-brand/80 transition">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="font-bold">Create New Course</span>
                    </a>
                </x-ui.card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Left Column: Recent Enrollments --}}
                <div class="lg:col-span-2 space-y-6">
                    <x-ui.card class="p-0 overflow-hidden">
                        <div class="p-4 border-b border-soft flex justify-between items-center">
                            <h3 class="font-bold text-lg text-dark">Recent Enrollments</h3>
                            <a href="{{ route('instructor.courses.index') }}" class="text-sm text-brand hover:underline">View All Courses</a>
                        </div>
                        <div class="divide-y divide-soft">
                            @forelse($recentEnrollments as $en)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold">
                                        {{ substr($en->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-dark">{{ $en->user->name }}</div>
                                        <div class="text-xs text-dark/60">Enrolled in <span class="font-medium text-brand">{{ $en->course->title }}</span></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-dark/50">{{ $en->created_at->diffForHumans() }}</div>
                                    @if($en->status === 'completed')
                                        <x-ui.badge color="green">Completed</x-ui.badge>
                                    @elseif($en->status === 'active')
                                        <x-ui.badge color="brand">Active</x-ui.badge>
                                    @else
                                        <x-ui.badge color="gray">{{ ucfirst($en->status) }}</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-dark/50">
                                No recent enrollments found.
                            </div>
                            @endforelse
                        </div>
                    </x-ui.card>
                </div>

                {{-- Right Column: Top Courses --}}
                <div class="space-y-6">
                    <x-ui.card class="p-0 overflow-hidden">
                        <div class="p-4 border-b border-soft">
                            <h3 class="font-bold text-lg text-dark">Top Courses</h3>
                        </div>
                        <div class="divide-y divide-soft">
                            @forelse($topCourses as $c)
                            <div class="p-4 hover:bg-gray-50 transition">
                                <div class="font-semibold text-dark mb-1">{{ $c->title }}</div>
                                <div class="flex justify-between items-center text-xs text-dark/60">
                                    <span>{{ $c->enrollments_count }} Students</span>
                                    @if($c->status === 'published')
                                        <span class="text-green-600 font-medium">Published</span>
                                    @else
                                        <span class="text-gray-500">Draft</span>
                                    @endif
                                </div>
                                <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5">
                                    {{-- Visual bar relative to max (simplified logic) --}}
                                    @php 
                                        $max = max($topCourses->first()->enrollments_count ?? 0, 1); 
                                        $pct = ($c->enrollments_count / $max) * 100; 
                                    @endphp
                                    <div class="bg-brand h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            @empty
                            <div class="p-6 text-center text-dark/50">
                                No courses yet.
                            </div>
                            @endforelse
                        </div>
                        <div class="p-3 bg-gray-50 text-center border-t border-soft">
                            <a href="{{ route('instructor.courses.index') }}" class="text-sm font-semibold text-brand hover:underline">Manage Courses</a>
                        </div>
                    </x-ui.card>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
