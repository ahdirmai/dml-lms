<x-app-layout :title="'Instructor Dashboard'">
    <x-slot name="header">
        Instructor Dashboard
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-ui.card class="p-5 border-l-4 border-l-brand">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Courses</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($totalCourses) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $publishedCourses }} Published</p>
                        </div>
                        <div class="p-3 bg-brand/10 text-brand rounded-xl">
                            <x-ui.icon name="book-open" class="w-6 h-6" />
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-5 border-l-4 border-l-accent">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Students</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($totalStudents) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Unique Enrollments</p>
                        </div>
                        <div class="p-3 bg-accent/10 text-accent rounded-xl">
                            <x-ui.icon name="users" class="w-6 h-6" />
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-5 border-l-4 border-l-emerald-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Completions</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($totalCompletions) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Finished Courses</p>
                        </div>
                        <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl">
                            <x-ui.icon name="check-circle" class="w-6 h-6" />
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-0 border border-brand/20 bg-brand/5 hover:bg-brand/10 transition-colors group cursor-pointer relative overflow-hidden">
                    <a href="{{ route('instructor.courses.create') }}" class="absolute inset-0 z-10"></a>
                    <div class="h-full flex flex-col items-center justify-center p-5 text-center">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition-transform">
                            <x-ui.icon name="plus" class="w-6 h-6 text-brand" />
                        </div>
                        <span class="font-bold text-brand">Create New Course</span>
                        <span class="text-xs text-brand/60 mt-1">Start building your content</span>
                    </div>
                </x-ui.card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Left Column: Recent Enrollments --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden">
                        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                                <x-ui.icon name="user-group" class="w-5 h-5 text-gray-400" />
                                Recent Enrollments
                            </h3>
                            <a href="{{ route('instructor.courses.index') }}" class="text-sm font-medium text-brand hover:text-brand-dark flex items-center gap-1">
                                View All <x-ui.icon name="arrow-right" class="w-3 h-3" />
                            </a>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($recentEnrollments as $en)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold border border-gray-200">
                                        {{ substr($en->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $en->user->name }}</div>
                                        <div class="text-xs text-gray-500">Enrolled in <a href="{{ route('instructor.courses.edit', $en->course_id) }}" class="font-medium text-brand hover:underline">{{ $en->course->title }}</a></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400 mb-1">{{ $en->created_at->diffForHumans() }}</div>
                                    @if($en->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                    @elseif($en->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($en->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="p-12 text-center text-gray-500">
                                <x-ui.icon name="users" class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                <p>No recent enrollments found.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Column: Top Courses --}}
                <div class="space-y-6">
                    <div class="bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden">
                        <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                                <x-ui.icon name="star" class="w-5 h-5 text-gray-400" />
                                Top Courses
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($topCourses as $c)
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <a href="{{ route('instructor.courses.edit', $c->id) }}" class="font-semibold text-gray-900 hover:text-brand line-clamp-1">{{ $c->title }}</a>
                                    @if($c->status === 'published')
                                        <x-ui.icon name="check-circle" class="w-4 h-4 text-green-500 shrink-0" title="Published" />
                                    @else
                                        <x-ui.icon name="pencil" class="w-4 h-4 text-gray-400 shrink-0" title="Draft" />
                                    @endif
                                </div>
                                <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                                    <span>{{ $c->enrollments_count }} Students</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    {{-- Visual bar relative to max --}}
                                    @php 
                                        $max = max($topCourses->first()->enrollments_count ?? 0, 1); 
                                        $pct = ($c->enrollments_count / $max) * 100; 
                                    @endphp
                                    <div class="bg-brand h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-gray-500">
                                <p>No courses yet.</p>
                            </div>
                            @endforelse
                        </div>
                        <div class="p-3 bg-gray-50 text-center border-t border-gray-100">
                            <a href="{{ route('instructor.courses.index') }}" class="text-sm font-semibold text-brand hover:text-brand-dark hover:underline">Manage Courses</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
