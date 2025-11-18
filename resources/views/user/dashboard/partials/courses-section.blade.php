{{-- resources/views/dashboard/partials/courses-section.blade.php --}}
<section class="space-y-6">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-custom-soft border border-gray-100">
        <h2 class="text-lg sm:text-xl font-bold mb-4 text-brand">
            Semua Kelas (<span id="filter-status">All</span>)
        </h2>

        {{-- Filter buttons --}}
        <div id="filter-buttons" class="flex space-x-2 md:space-x-4 mb-6 overflow-x-auto scrollbar-hide">
            @foreach(['All','Completed','In Progress','Not Started','Expired'] as $filter)
            <button onclick="filterCourses(event, '{{ $filter }}')"
                class="filter-btn flex-shrink-0 px-4 py-2 text-xs sm:text-sm font-medium rounded-full transition-colors duration-200
                        {{ $filter === 'All' ? 'bg-brand text-white shadow-md' : 'bg-white text-gray-700 hover:bg-soft border border-gray-200' }}"
                data-filter="{{ $filter }}">
                {{ $filter }}
            </button>
            @endforeach
        </div>

        {{-- Course list --}}
        <div id="course-list-container" class="space-y-5">
            @forelse($courses as $course)
            @php
            $courseId = $course['id'];
            $detailUrl = route('user.courses.show', $courseId);
            @endphp
            <div class="course-card bg-white p-4 sm:p-5 rounded-xl shadow-custom-soft border border-gray-100 flex flex-col md:flex-row transition transform hover:scale-[1.01] duration-300 hover:shadow-lg"
                data-status="{{ $course['status'] }}" style="display:flex;">
                {{-- Thumbnail --}}
                <div
                    class="w-full md:w-56 h-36 bg-soft rounded-lg overflow-hidden flex-shrink-0 mb-4 md:mb-0 md:mr-6 relative">
                    <img src="https://placehold.co/400x300/09759A/FFFFFF?text=LMS&font=inter"
                        alt="{{ $course['title'] }}" class="w-full h-full object-cover">
                    <div class="absolute top-2 left-2 badge bg-brand text-white font-semibold text-[0.7rem]">
                        {{ $course['category'] }}
                    </div>
                </div>

                {{-- Detail --}}
                <div class="flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800 hover:text-brand transition-colors">
                            <a href="{{ $detailUrl }}">{{ $course['title'] }}</a>
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 mb-3">
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
                                {{ $course['totalDuration'] }} min
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
                            @switch($course['status'])
                            @case('Completed')
                            <span class="badge badge-completed">
                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                Completed
                            </span>
                            @break
                            @case('In Progress')
                            <span class="badge badge-in-progress">
                                <i data-lucide="loader" class="w-3 h-3 mr-1 animate-spin"></i>
                                In Progress
                            </span>
                            @break
                            @case('Not Started')
                            <span class="badge badge-not-started">
                                <i data-lucide="circle-dot" class="w-3 h-3 mr-1"></i>
                                Not Started
                            </span>
                            @break
                            @case('Expired')
                            <span class="badge badge-expired">
                                <i data-lucide="alert-triangle" class="w-3 h-3 mr-1"></i>
                                Expired
                            </span>
                            @break
                            @default
                            <span class="badge">
                                {{ $course['status'] }}
                            </span>
                            @endswitch
                        </div>
                    </div>

                    {{-- Progress + CTA --}}
                    <div
                        class="mt-4 md:mt-0 pt-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                        <div class="flex-grow w-full sm:w-auto sm:mr-6">
                            <p class="text-[11px] sm:text-xs font-medium mb-1 text-gray-600">
                                Progress: {{ $course['progress'] }}%
                            </p>
                            <div class="w-full bg-soft rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full {{ $course['progress'] === 100 ? 'bg-green-500' : 'bg-brand' }}"
                                    style="width:{{ $course['progress'] }}%">
                                </div>
                            </div>
                        </div>

                        <div class="w-full sm:w-auto">
                            @php
                            $modules = collect($course['modules'] ?? []);
                            $allModulesDone = $modules->every(fn($m) => ($m['status'] ?? '') === 'completed');

                            $hasPreTest = !empty($course['preTest']) && !empty($course['submit_pre_url']);
                            $hasPostTest = !empty($course['postTest']) && !empty($course['submit_post_url']);
                            @endphp

                            @if($course['status'] === 'Not Started')
                            @if($hasPreTest)
                            {{-- Mulai Pre-Test di modal --}}
                            <x-ui.button variant="primary" onclick="window.TestFlow?.startPreTest('{{ $courseId }}')">
                                Start Pre-Test
                            </x-ui.button>
                            @else
                            {{-- Kalau nggak ada pre-test, langsung ke detail kursus --}}
                            <x-ui.button as="a" href="{{ $detailUrl }}" variant="primary">
                                Mulai Belajar
                            </x-ui.button>
                            @endif

                            @elseif($course['status'] === 'In Progress')
                            @if($allModulesDone && empty($course['postTestScore']) && $modules->count() > 0 &&
                            $hasPostTest)
                            {{-- Semua modul selesai & belum ada post-test --}}
                            <x-ui.button variant="primary" onclick="window.TestFlow?.startPostTest('{{ $courseId }}')">
                                Lanjut Post-Test
                            </x-ui.button>
                            @else
                            {{-- Lanjut nonton / belajar biasa --}}
                            <x-ui.button as="a" href="{{ $detailUrl }}" variant="primary">
                                Continue
                            </x-ui.button>
                            @endif

                            @elseif($course['status'] === 'Completed')
                            @if(!empty($course['submit_review_url']))
                            <x-ui.button variant="secondary" onclick="window.TestFlow?.openReview('{{ $courseId }}')">
                                Review
                            </x-ui.button>
                            @else
                            <x-ui.button as="a" href="{{ $detailUrl }}" variant="secondary">
                                Lihat Detail
                            </x-ui.button>
                            @endif

                            @elseif($course['status'] === 'Expired')
                            <x-ui.button variant="subtle" disabled title="Hubungi admin untuk perpanjangan">
                                Expired
                            </x-ui.button>

                            @else
                            <x-ui.button variant="subtle" disabled>
                                Status: {{ $course['status'] }}
                            </x-ui.button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div id="no-courses-placeholder" class="text-center text-gray-500 py-10">
                <i data-lucide="folder-search" class="w-12 h-12 mx-auto mb-4"></i>
                <p class="font-medium">Tidak ada kursus</p>
                <p class="text-sm">Saat ini tidak ada kursus yang ditugaskan untuk Anda.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

@push('styles')
<style>
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
    }

    .badge-completed {
        background-color: #d1fae5;
        color: #059669;
    }

    .badge-in-progress {
        background-color: #fefce8;
        color: #d97706;
    }

    .badge-not-started {
        background-color: #e5e7eb;
        color: #4b5563;
    }

    .badge-expired {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@push('scripts')
<script>
    const courseListContainer = document.getElementById('course-list-container');
    const filterButtonsContainer = document.getElementById('filter-buttons');
    const filterStatusEl = document.getElementById('filter-status');
    const noCoursesPlaceholder = document.getElementById('no-courses-placeholder');

    function filterCourses(event, filter='All'){
        if(event) event.preventDefault();
        if(filterStatusEl) filterStatusEl.textContent = filter;

        if(filterButtonsContainer) {
            filterButtonsContainer.querySelectorAll('button.filter-btn').forEach(btn => {
                if (btn.dataset.filter === filter) {
                    btn.classList.add('bg-brand', 'text-white', 'shadow-md');
                    btn.classList.remove('bg-white', 'text-gray-700', 'hover:bg-soft', 'border', 'border-gray-200');
                } else {
                    btn.classList.remove('bg-brand', 'text-white', 'shadow-md');
                    btn.classList.add('bg-white', 'text-gray-700', 'hover:bg-soft', 'border', 'border-gray-200');
                }
            });
        }

        let hasResults = false;
        if(courseListContainer) {
            const cards = courseListContainer.querySelectorAll('.course-card');
            cards.forEach(card => {
                const status = card.dataset.status;
                if (filter === 'All' || status === filter) {
                    card.style.display = 'flex';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });
        }

        let noResultEl = courseListContainer ? courseListContainer.querySelector('.no-results-placeholder') : null;
        if (!hasResults && courseListContainer) {
            if (!noResultEl) {
                noResultEl = document.createElement('div');
                noResultEl.className = 'no-results-placeholder text-center text-gray-500 py-10';
                courseListContainer.appendChild(noResultEl);
            }
            noResultEl.innerHTML = `
                <i data-lucide="folder-search" class="w-12 h-12 mx-auto mb-4"></i>
                <p class="font-medium">Tidak ada kursus</p>
                <p class="text-sm">Tidak ada kursus yang ditemukan untuk filter "${filter}".</p>`;
            if (window.lucide) window.lucide.createIcons();
        } else if (noResultEl) {
            noResultEl.remove();
        }

        if (noCoursesPlaceholder) {
            noCoursesPlaceholder.style.display = (hasResults || filter !== 'All') ? 'none' : 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        filterCourses(null, 'All');
    });
</script>
@endpush