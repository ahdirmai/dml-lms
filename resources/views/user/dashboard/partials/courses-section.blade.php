{{-- resources/views/dashboard/partials/courses-section.blade.php --}}
<section class="space-y-6" id="courses-section">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-custom-soft border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <h2 class="text-lg sm:text-xl font-bold text-brand whitespace-nowrap">
                    Semua Kelas (<span id="filtered-count">{{ count($courses) }}</span>)
                </h2>
                
                {{-- Search Input --}}
                <div class="relative w-full sm:w-64">
                    <input type="text" id="course-search" placeholder="Cari kursus..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all">
                    <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                </div>
            </div>

            {{-- Filter buttons --}}
            <div class="flex space-x-2 overflow-x-auto scrollbar-hide pb-2 sm:pb-0" id="course-filters">
                @foreach(['All', 'Completed', 'In Progress', 'Not Started', 'Expired'] as $filter)
                <button data-filter="{{ $filter }}"
                    class="filter-btn flex-shrink-0 px-4 py-2 text-xs sm:text-sm font-medium rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand/20 {{ $filter === 'All' ? 'bg-brand text-white shadow-md' : 'bg-white text-gray-700 hover:bg-soft border border-gray-200' }}">
                    {{ $filter }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Course list --}}
        <div class="space-y-5 min-h-[200px]" id="course-list-container">
            @forelse($courses as $course)
            @php
            $courseId = $course['id'];
            $detailUrl = route('user.courses.show', $courseId);
            $status = $course['status'] ?? 'Not Started';
            @endphp
            <div data-id="{{ $courseId }}" 
                 data-status="{{ $status }}" 
                 data-title="{{ strtolower($course['title']) }}" 
                 data-category="{{ strtolower($course['category'] ?? '') }}"
                 class="course-card bg-white p-4 sm:p-5 rounded-xl shadow-custom-soft border border-gray-100 flex flex-col md:flex-row transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group">

                {{-- Thumbnail --}}
                <div
                    class="w-full md:w-56 h-36 bg-soft rounded-lg overflow-hidden flex-shrink-0 mb-4 md:mb-0 md:mr-6 relative group-hover:ring-2 group-hover:ring-brand/20 transition-all">
                    @if($course['thumbnail_path'])
                        <img src="{{ Storage::url($course['thumbnail_path']) }}" alt="{{ $course['title'] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    @endif
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
                            @if($course['hasReviewed'])
                            <x-ui.button as="a" href="{{ $course['certificateUrl'] }}" target="_blank" variant="success" size="sm">
                                <i data-lucide="award" class="w-3.5 h-3.5 mr-1"></i>
                                Lihat Sertifikat
                            </x-ui.button>
                            @elseif(!empty($course['submit_review_url']))
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
            <div id="empty-state" class="hidden text-center text-gray-500 py-10 transition-all duration-300">
                <i data-lucide="filter-x" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                <p class="font-medium">Tidak ada kursus ditemukan</p>
                <p class="text-sm">Tidak ada kursus yang cocok dengan pencarian atau filter Anda.</p>
                <button id="reset-filter-btn" class="mt-4 text-brand hover:underline text-sm font-medium">
                    Reset Filter
                </button>
            </div>

            {{-- Pagination Controls --}}
            <div id="pagination-controls" class="hidden flex items-center justify-between border-t border-gray-100 pt-4">
                <div class="text-xs text-gray-500">
                    Menampilkan <span id="pag-start">0</span> - 
                    <span id="pag-end">0</span> 
                    dari <span id="pag-total">0</span> kursus
                </div>
                <div class="flex gap-1" id="pagination-buttons">
                    {{-- Generated by JS --}}
                </div>
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        class CourseManager {
            constructor() {
                this.cards = Array.from(document.querySelectorAll('.course-card'));
                this.itemsPerPage = 6;
                this.currentPage = 1;
                this.activeFilter = 'All';
                this.searchQuery = '';
                
                this.init();
            }

            init() {
                // Event Listeners
                const searchInput = document.getElementById('course-search');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        this.searchQuery = e.target.value.toLowerCase();
                        this.currentPage = 1;
                        this.render();
                    });
                }

                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        this.activeFilter = e.target.dataset.filter;
                        this.currentPage = 1;
                        this.updateFilterButtons();
                        this.render();
                    });
                });

                const resetBtn = document.getElementById('reset-filter-btn');
                if (resetBtn) {
                    resetBtn.addEventListener('click', () => {
                        this.activeFilter = 'All';
                        this.searchQuery = '';
                        if (searchInput) searchInput.value = '';
                        this.currentPage = 1;
                        this.updateFilterButtons();
                        this.render();
                    });
                }

                this.render();
            }

            updateFilterButtons() {
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    if (btn.dataset.filter === this.activeFilter) {
                        btn.classList.remove('bg-white', 'text-gray-700', 'hover:bg-soft', 'border', 'border-gray-200');
                        btn.classList.add('bg-brand', 'text-white', 'shadow-md');
                    } else {
                        btn.classList.add('bg-white', 'text-gray-700', 'hover:bg-soft', 'border', 'border-gray-200');
                        btn.classList.remove('bg-brand', 'text-white', 'shadow-md');
                    }
                });
            }

            getFilteredCards() {
                return this.cards.filter(card => {
                    const status = card.dataset.status;
                    const title = card.dataset.title || '';
                    const category = card.dataset.category || '';

                    const matchesStatus = this.activeFilter === 'All' || status === this.activeFilter;
                    const matchesSearch = title.includes(this.searchQuery) || category.includes(this.searchQuery);

                    return matchesStatus && matchesSearch;
                });
            }

            render() {
                const filtered = this.getFilteredCards();
                const total = filtered.length;
                const totalPages = Math.ceil(total / this.itemsPerPage);

                // Hide all first
                this.cards.forEach(card => card.classList.add('hidden'));

                // Calculate pagination
                const start = (this.currentPage - 1) * this.itemsPerPage;
                const end = start + this.itemsPerPage;
                const paginated = filtered.slice(start, end);

                // Show paginated items
                paginated.forEach(card => card.classList.remove('hidden'));

                // Update UI counts
                const countEl = document.getElementById('filtered-count');
                if (countEl) countEl.textContent = total;
                
                const pagStartEl = document.getElementById('pag-start');
                if (pagStartEl) pagStartEl.textContent = total > 0 ? start + 1 : 0;
                
                const pagEndEl = document.getElementById('pag-end');
                if (pagEndEl) pagEndEl.textContent = Math.min(end, total);
                
                const pagTotalEl = document.getElementById('pag-total');
                if (pagTotalEl) pagTotalEl.textContent = total;

                // Empty state
                const emptyState = document.getElementById('empty-state');
                if (emptyState) {
                    if (total === 0 && this.cards.length > 0) {
                        emptyState.classList.remove('hidden');
                    } else {
                        emptyState.classList.add('hidden');
                    }
                }

                // Pagination Controls
                const paginationControls = document.getElementById('pagination-controls');
                if (paginationControls) {
                    if (totalPages > 1) {
                        paginationControls.classList.remove('hidden');
                        this.renderPaginationButtons(totalPages);
                    } else {
                        paginationControls.classList.add('hidden');
                    }
                }
                
                // Re-init icons if needed (Lucide)
                if (window.lucide) window.lucide.createIcons();
            }

            renderPaginationButtons(totalPages) {
                const container = document.getElementById('pagination-buttons');
                if (!container) return;
                
                container.innerHTML = '';

                // Prev
                const prevBtn = document.createElement('button');
                prevBtn.textContent = 'Previous';
                prevBtn.className = `px-3 py-1 rounded-md text-xs font-medium border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed`;
                prevBtn.disabled = this.currentPage === 1;
                prevBtn.onclick = () => {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.render();
                    }
                };
                container.appendChild(prevBtn);

                // Pages
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    const isActive = i === this.currentPage;
                    btn.className = `w-8 h-8 flex items-center justify-center rounded-md text-xs font-medium border transition-colors ${isActive ? 'bg-brand text-white border-brand' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'}`;
                    btn.onclick = () => {
                        this.currentPage = i;
                        this.render();
                    };
                    container.appendChild(btn);
                }

                // Next
                const nextBtn = document.createElement('button');
                nextBtn.textContent = 'Next';
                nextBtn.className = `px-3 py-1 rounded-md text-xs font-medium border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed`;
                nextBtn.disabled = this.currentPage === totalPages;
                nextBtn.onclick = () => {
                    if (this.currentPage < totalPages) {
                        this.currentPage++;
                        this.render();
                    }
                };
                container.appendChild(nextBtn);
            }
        }

        new CourseManager();
    });
</script>
@endpush