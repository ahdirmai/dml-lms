<x-app-layout :title="'Admin Dashboard'">
    <x-slot name="header">
        Admin Dashboard
    </x-slot>

    <div class="py-4">
        <div class="mx-auto">

            {{-- ===== Overview Statistics Cards ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                {{-- Total Users --}}
                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Total Users</p>
                            <p class="mt-1 text-2xl font-bold text-dark">{{ number_format($totalUsers) }}</p>
                            <p class="mt-1 text-xs text-dark/50">
                                <span class="text-brand font-medium">+{{ number_format($thisWeekUsers) }}</span> this week
                            </p>
                        </div>
                        <div class="rounded-xl p-2 bg-brand/10 text-brand border border-brand/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Total Courses --}}
                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Total Courses</p>
                            <p class="mt-1 text-2xl font-bold text-dark">{{ number_format($totalCourses) }}</p>
                            <p class="mt-1 text-xs text-dark/50">
                                <span class="text-accent font-medium">{{ number_format($publishedCourses) }}</span> published
                            </p>
                        </div>
                        <div class="rounded-xl p-2 bg-accent/10 text-accent border border-accent/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Total Enrollments --}}
                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Total Enrollments</p>
                            <p class="mt-1 text-2xl font-bold text-dark">{{ number_format($totalEnrollments) }}</p>
                            <p class="mt-1 text-xs text-dark/50">
                                <span class="text-brand font-medium">+{{ number_format($thisWeekEnrollments) }}</span> this week
                            </p>
                        </div>
                        <div class="rounded-xl p-2 bg-purple-100 text-purple-600 border border-purple-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Completion Rate --}}
                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Completion Rate</p>
                            <p class="mt-1 text-2xl font-bold text-dark">{{ number_format($completionRate, 1) }}%</p>
                            <p class="mt-1 text-xs text-dark/50">
                                <span class="text-accent font-medium">{{ number_format($completedEnrollments) }}</span> completed
                            </p>
                        </div>
                        <div class="rounded-xl p-2 bg-green-100 text-green-600 border border-green-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- ===== Secondary Statistics ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Students</p>
                            <p class="mt-1 text-xl font-bold text-dark">{{ number_format($studentCount) }}</p>
                        </div>
                        <div class="rounded-xl p-2 bg-blue-100 text-blue-600 border border-blue-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Instructors</p>
                            <p class="mt-1 text-xl font-bold text-dark">{{ number_format($instructorCount) }}</p>
                        </div>
                        <div class="rounded-xl p-2 bg-indigo-100 text-indigo-600 border border-indigo-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-dark/60">Avg Progress</p>
                            <p class="mt-1 text-xl font-bold text-dark">{{ number_format($avgProgress, 1) }}%</p>
                        </div>
                        <div class="rounded-xl p-2 bg-yellow-100 text-yellow-600 border border-yellow-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- ===== Charts Row ===== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
                {{-- Monthly Enrollment Trend --}}
                <x-ui.card class="p-4">
                    <h3 class="text-sm font-semibold text-dark mb-3 uppercase tracking-wider">Monthly Enrollment Trend</h3>
                    <div class="h-64">
                        @if($monthlyEnrollments->isNotEmpty())
                            <div class="flex items-end justify-between h-full space-x-2">
                                @php
                                    $maxCount = $monthlyEnrollments->max('count');
                                @endphp
                                @foreach($monthlyEnrollments as $data)
                                    @php
                                        $height = $maxCount > 0 ? ($data->count / $maxCount) * 100 : 0;
                                    @endphp
                                    <div class="flex-1 flex flex-col items-center justify-end h-full">
                                        <div class="w-full bg-brand rounded-t-lg hover:brightness-95 transition-all cursor-pointer relative group"
                                             style="height: {{ $height }}%">
                                            <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-dark text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                {{ $data->count }} enrollments
                                            </div>
                                        </div>
                                        <div class="mt-2 text-xs text-dark/60 text-center">
                                            {{ \Carbon\Carbon::parse($data->month)->format('M') }}
                                        </div>
                                        <div class="text-xs font-semibold text-dark">
                                            {{ $data->count }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center justify-center h-full text-dark/50 text-sm">
                                No enrollment data available
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                {{-- User Growth Trend --}}
                <x-ui.card class="p-4">
                    <h3 class="text-sm font-semibold text-dark mb-3 uppercase tracking-wider">User Growth Trend</h3>
                    <div class="h-64">
                        @if($userGrowth->isNotEmpty())
                            <div class="flex items-end justify-between h-full space-x-2">
                                @php
                                    $maxCount = $userGrowth->max('count');
                                @endphp
                                @foreach($userGrowth as $data)
                                    @php
                                        $height = $maxCount > 0 ? ($data->count / $maxCount) * 100 : 0;
                                    @endphp
                                    <div class="flex-1 flex flex-col items-center justify-end h-full">
                                        <div class="w-full bg-accent rounded-t-lg hover:brightness-95 transition-all cursor-pointer relative group"
                                             style="height: {{ $height }}%">
                                            <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-dark text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                {{ $data->count }} new users
                                            </div>
                                        </div>
                                        <div class="mt-2 text-xs text-dark/60 text-center">
                                            {{ \Carbon\Carbon::parse($data->month)->format('M') }}
                                        </div>
                                        <div class="text-xs font-semibold text-dark">
                                            {{ $data->count }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center justify-center h-full text-dark/50 text-sm">
                                No user growth data available
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>

            {{-- ===== Top Courses & Category Distribution ===== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
                {{-- Top 5 Most Enrolled Courses --}}
                <x-ui.card class="p-4">
                    <h3 class="text-sm font-semibold text-dark mb-3 uppercase tracking-wider">Top 5 Most Enrolled Courses</h3>
                    <div class="space-y-3">
                        @forelse($topCourses as $index => $course)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-soft transition-colors">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-brand/10 text-brand font-bold text-sm shrink-0">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-dark truncate">{{ $course->title }}</p>
                                    <p class="text-xs text-dark/60">
                                        By {{ $course->instructor->name ?? 'N/A' }} •
                                        @if($course->status === 'published')
                                            <span class="text-accent">Published</span>
                                        @else
                                            <span class="text-dark/50">Draft</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    <x-ui.badge color="brand">{{ number_format($course->enrollments_count) }}</x-ui.badge>
                                </div>
                            </div>
                        @empty
                            <p class="text-dark/50 text-sm text-center py-4">No courses available</p>
                        @endforelse
                    </div>
                </x-ui.card>

                {{-- Category Distribution --}}
                <x-ui.card class="p-4">
                    <h3 class="text-sm font-semibold text-dark mb-3 uppercase tracking-wider">Category Distribution</h3>
                    <div class="space-y-3">
                        @forelse($categoryDistribution as $category)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-dark">{{ $category->name }}</span>
                                    <span class="text-sm font-semibold text-dark">{{ $category->courses_count }}</span>
                                </div>
                                <div class="w-full bg-soft rounded-full h-2">
                                    @php
                                        $maxCourses = $categoryDistribution->max('courses_count');
                                        $percentage = $maxCourses > 0 ? ($category->courses_count / $maxCourses) * 100 : 0;
                                    @endphp
                                    <div class="bg-accent h-2 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-dark/50 text-sm text-center py-4">No categories available</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            {{-- ===== Recent Activities & Recent Users ===== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
                {{-- Recent Activities --}}
                <x-ui.card class="p-4">
                    <h3 class="text-sm font-semibold text-dark mb-3 uppercase tracking-wider">Recent Enrollments</h3>
                    <div class="space-y-2">
                        @forelse($recentActivities as $activity)
                            <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-soft transition-colors text-sm">
                                <div class="shrink-0 mt-0.5">
                                    <div class="w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-dark truncate">{{ $activity->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-dark/60 truncate">{{ $activity->course->title ?? 'N/A' }}</p>
                                    <p class="text-xs text-dark/50 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="shrink-0">
                                    @php
                                        $statusColors = [
                                            'active' => 'brand',
                                            'completed' => 'accent',
                                            'pending' => 'gray',
                                        ];
                                        $color = $statusColors[$activity->status] ?? 'gray';
                                    @endphp
                                    <x-ui.badge :color="$color">{{ ucfirst($activity->status) }}</x-ui.badge>
                                </div>
                            </div>
                        @empty
                            <p class="text-dark/50 text-sm text-center py-4">No recent activities</p>
                        @endforelse
                    </div>
                </x-ui.card>

                {{-- Recent Users --}}
                <x-ui.card class="p-4">
                    <h3 class="text-sm font-semibold text-dark mb-3 uppercase tracking-wider">Recent New Users</h3>
                    <div class="space-y-2">
                        @forelse($recentUsers as $user)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-soft transition-colors">
                                <div class="shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-accent/10 text-accent flex items-center justify-center font-semibold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-dark truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-dark/60 truncate">{{ $user->email }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-xs text-dark/50">{{ $user->created_at->diffForHumans() }}</p>
                                    @if($user->roles->isNotEmpty())
                                        <x-ui.badge color="dark" class="mt-1">{{ $user->roles->first()->name }}</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-dark/50 text-sm text-center py-4">No recent users</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            {{-- ===== Enrollment Status Breakdown ===== --}}
            <x-ui.card class="p-4">
                <h3 class="text-sm font-semibold text-dark mb-3 uppercase tracking-wider">Enrollment Status Breakdown</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 rounded-lg bg-blue-50 border border-blue-100">
                        <p class="text-xs uppercase tracking-wider text-blue-600 mb-1">Active</p>
                        <p class="text-3xl font-bold text-blue-700">{{ number_format($activeEnrollments) }}</p>
                        <p class="text-xs text-blue-600 mt-1">
                            {{ $totalEnrollments > 0 ? number_format(($activeEnrollments / $totalEnrollments) * 100, 1) : 0 }}% of total
                        </p>
                    </div>
                    <div class="text-center p-4 rounded-lg bg-green-50 border border-green-100">
                        <p class="text-xs uppercase tracking-wider text-green-600 mb-1">Completed</p>
                        <p class="text-3xl font-bold text-green-700">{{ number_format($completedEnrollments) }}</p>
                        <p class="text-xs text-green-600 mt-1">
                            {{ $totalEnrollments > 0 ? number_format(($completedEnrollments / $totalEnrollments) * 100, 1) : 0 }}% of total
                        </p>
                    </div>
                    <div class="text-center p-4 rounded-lg bg-yellow-50 border border-yellow-100">
                        <p class="text-xs uppercase tracking-wider text-yellow-600 mb-1">Pending</p>
                        <p class="text-3xl font-bold text-yellow-700">{{ number_format($pendingEnrollments) }}</p>
                        <p class="text-xs text-yellow-600 mt-1">
                            {{ $totalEnrollments > 0 ? number_format(($pendingEnrollments / $totalEnrollments) * 100, 1) : 0 }}% of total
                        </p>
                    </div>
                </div>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
