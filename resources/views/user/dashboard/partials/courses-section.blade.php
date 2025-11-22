{{-- resources/views/dashboard/partials/courses-section.blade.php --}}
<section class="space-y-6" x-data="courseFilter()">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-custom-soft border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
            <h2 class="text-lg sm:text-xl font-bold text-brand">
                Semua Kelas (<span x-text="activeFilter">All</span>)
            </h2>

            {{-- Filter buttons --}}
            <div class="flex space-x-2 overflow-x-auto scrollbar-hide pb-2 sm:pb-0">
                @foreach(['All', 'Completed', 'In Progress', 'Not Started', 'Expired'] as $filter)
                <button @click="setFilter('{{ $filter }}')"
                    :class="activeFilter === '{{ $filter }}' ? 'bg-brand text-white shadow-md' : 'bg-white text-gray-700 hover:bg-soft border border-gray-200'"
                    class="flex-shrink-0 px-4 py-2 text-xs sm:text-sm font-medium rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand/20"
                    aria-pressed="{{ $filter === 'All' ? 'true' : 'false' }}"
                    :aria-pressed="activeFilter === '{{ $filter }}'">
                    {{ $filter }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Course list --}}
        <div class="space-y-5 min-h-[200px]">
            @forelse($courses as $course)
            @php
            $courseId = $course['id'];
            $detailUrl = route('user.courses.show', $courseId);
            $status = $course['status'] ?? 'Not Started';
            @endphp
            <div x-show="shouldShow('{{ $status }}')" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                class="bg-white p-4 sm:p-5 rounded-xl shadow-custom-soft border border-gray-100 flex flex-col md:flex-row transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group">

                {{-- Thumbnail --}}
                <div
                    class="w-full md:w-56 h-36 bg-soft rounded-lg overflow-hidden flex-shrink-0 mb-4 md:mb-0 md:mr-6 relative group-hover:ring-2 group-hover:ring-brand/20 transition-all">
                    <img src="https://placehold.co/400x300/09759A/FFFFFF?text=LMS&font=inter"
                        alt="{{ $course['title'] }}" class="w-full h-full object-cover">
                    <div class="absolute top-2 left-2 badge bg-brand text-white font-semibold text-[0.7rem] shadow-sm">
                        {{ $course['category'] }}
                    </div>
                </div>

                {{-- Detail --}}
                <div class="flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800 group-hover:text-brand transition-colors">
                            <a href="{{ $detailUrl }}" class="focus:outline-none focus:underline">
                                {{ $course['title'] }}
                            </a>
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 mb-3 line-clamp-2">
                            {{ $course['subtitle'] }}
                        </p>

                        <div
                            class="flex flex-wrap items-center text-[11px] sm:text-xs text-gray-600 gap-x-4 gap-y-1 mb-3">
                            <div class="flex items-center" title="Total Modul">
                                <i data-lucide="video" class="w-4 h-4 mr-1 text-gray-400"></i>
                                {{ $course['totalModules'] }} Modul
                            </div>
                            <div class="flex items-center" title="Total Durasi">
                                <i data-lucide="clock" class="w-4 h-4 mr-1 text-gray-400"></i>
                                {{ $course['totalDuration'] }}
                            </div>
                            <div class="flex items-center" title="Diberikan oleh">
                                <i data-lucide="user-check" class="w-4 h-4 mr-1 text-gray-400"></i>
                                {{ $course['assignedBy'] }}
                            </div>
                        </div>

                        {{-- Pre/Post + status badge --}}
                        <div class="flex flex-wrap items-center gap-3 mb-3">
                            <div class="text-xs sm:text-sm font-medium">
                                Pre-Test:
                                <span
                                    class="{{ isset($course['preTestScore']) && $course['preTestScore'] !== null ? 'font-bold text-green-600' : 'italic text-gray-400' }}">
                                    {{ $course['preTestScore'] ?? '-' }}
                                </span>
                            </div>
                            <div class="text-xs sm:text-sm font-medium">
                                Post-Test:
                                <span
                                    class="{{ isset($course['postTestScore']) && $course['postTestScore'] !== null ? 'font-bold text-green-600' : 'italic text-gray-400' }}">
                                    {{ $course['postTestScore'] ?? '-' }}
                                </span>
                            </div>

                            {{-- Status badge --}}
                            @php
                            $badgeClass = match($status) {
                            'Completed' => 'bg-green-100 text-green-700',
                            'In Progress' => 'bg-amber-50 text-amber-600',
                            'Not Started' => 'bg-gray-100 text-gray-600',
                            'Expired' => 'bg-red-100 text-red-600',
                            default => 'bg-gray-100 text-gray-600'
                            };
                            $icon = match($status) {
                            'Completed' => 'check-circle',
                            'In Progress' => 'loader',
                            'Not Started' => 'circle-dot',
                            'Expired' => 'alert-triangle',
                            default => 'circle'
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                <i data-lucide="{{ $icon }}" class="w-3 h-3 mr-1 {{ $status === 'In Progress' ? 'animate-spin' : '' }}"></i>
                                {{ $status }}
                            </span>
                        </div>
                    </div>

                    {{-- Progress + CTA --}}
                    <div
                        class="mt-4 md:mt-0 pt-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                        <div class="flex-grow w-full sm:w-auto sm:mr-6">
                            <div class="flex justify-between text-[11px] sm:text-xs font-medium mb-1 text-gray-600">
                                <span>Progress</span>
                                <span>{{ $course['progress'] }}%</span>
                            </div>
                            <div class="w-full bg-soft rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500 {{ $course['progress'] === 100 ? 'bg-green-500' : 'bg-brand' }}"
                                    style="width:{{ $course['progress'] }}%">
                                </div>
                            </div>
                        </div>

                        <div class="w-full sm:w-auto flex justify-end">
                            @php
                            $modules = collect($course['modules'] ?? []);
                            $allModulesDone = $modules->every(fn($m) => ($m['status'] ?? '') === 'completed');

                            $hasPreTest = !empty($course['preTest']) && !empty($course['submit_pre_url']);
                            $hasPostTest = !empty($course['postTest']) && !empty($course['submit_post_url']);
                            @endphp

                            @if($status === 'Not Started')
                            @if($hasPreTest)
                            <x-ui.button variant="primary" size="sm" onclick="window.TestFlow?.startPreTest('{{ $courseId }}')">
                                Start Pre-Test
                            </x-ui.button>
                            @else
                            <x-ui.button as="a" href="{{ $detailUrl }}" variant="primary" size="sm">
                                Mulai Belajar
                            </x-ui.button>
                            @endif

                            @elseif($status === 'In Progress')
                            @if($allModulesDone && empty($course['postTestScore']) && $modules->count() > 0 &&
                            $hasPostTest)
                            <x-ui.button variant="primary" size="sm" onclick="window.TestFlow?.startPostTest('{{ $courseId }}')">
                                Lanjut Post-Test
                            </x-ui.button>
                            @else
                            <x-ui.button as="a" href="{{ $detailUrl }}" variant="primary" size="sm">
                                Continue
                            </x-ui.button>
                            @endif

                            @elseif($status === 'Completed')
                            @if(!empty($course['submit_review_url']))
                            <x-ui.button variant="secondary" size="sm" onclick="window.TestFlow?.openReview('{{ $courseId }}')">
                                Review
                            </x-ui.button>
                            @else
                            <x-ui.button as="a" href="{{ $detailUrl }}" variant="secondary" size="sm">
                                Lihat Detail
                            </x-ui.button>
                            @endif

                            @elseif($status === 'Expired')
                            <x-ui.button variant="subtle" size="sm" disabled title="Hubungi admin untuk perpanjangan">
                                Expired
                            </x-ui.button>

                            @else
                            <x-ui.button variant="subtle" size="sm" disabled>
                                {{ $status }}
                            </x-ui.button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-gray-500 py-10">
                <i data-lucide="folder-search" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                <p class="font-medium">Tidak ada kursus</p>
                <p class="text-sm">Saat ini tidak ada kursus yang ditugaskan untuk Anda.</p>
            </div>
            @endforelse

            {{-- Empty state for filter --}}
            <div x-show="filteredCount === 0 && totalCourses > 0" x-cloak
                class="text-center text-gray-500 py-10 transition-all duration-300">
                <i data-lucide="filter-x" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                <p class="font-medium">Tidak ada kursus ditemukan</p>
                <p class="text-sm">Tidak ada kursus dengan status "<span x-text="activeFilter"></span>".</p>
                <button @click="setFilter('All')" class="mt-4 text-brand hover:underline text-sm font-medium">
                    Tampilkan Semua
                </button>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('courseFilter', () => ({
            activeFilter: 'All',
            totalCourses: {{ count($courses) }},
            
            init() {
                // Re-initialize icons when filter changes or DOM updates
                this.$watch('activeFilter', () => {
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                });
            },

            setFilter(filter) {
                this.activeFilter = filter;
            },

            shouldShow(status) {
                if (this.activeFilter === 'All') return true;
                return status === this.activeFilter;
            },

            get filteredCount() {
                if (this.activeFilter === 'All') return this.totalCourses;
                // Note: This is a simplified count. For exact count in JS, we'd need to pass the data array to Alpine.
                // But since we are using x-show on DOM elements, we can count visible elements if needed.
                // For simplicity and performance, we can use a query selector count.
                const cards = document.querySelectorAll('.course-card'); // Assuming we add this class back or use a specific selector
                // Actually, let's pass the courses data status to Alpine for accurate counting without DOM query spam
                // Or simpler: query the DOM for visible elements matching the status?
                // Let's do the robust way: pass simplified data to Alpine.
                return this.coursesData.filter(c => this.activeFilter === 'All' || c.status === this.activeFilter).length;
            },

            coursesData: @json(collect($courses)->map(fn($c) => ['status' => $c['status'] ?? 'Not Started']))
        }));
    });
</script>
@endpush