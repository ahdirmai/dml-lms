{{-- resources/views/user/courses/index.blade.php --}}
<x-app-layout :title="'Kursus Saya'">
    <x-slot name="header">
        {{ __('Kursus Saya') }}
    </x-slot>

    @php
    $tabs = $tabs ?? [];
    $counts = $counts ?? [];
    $activeTab = $activeTab ?? 'in_progress';
    $tabUrl = fn($key) => route('user.courses.index', ['tab' => $key]);
    @endphp

    <div x-data="courseSearch()">
        {{-- HEADER: Search + info --}}
        <section class="mb-6 space-y-4">
            <div
                class="bg-white p-5 sm:p-6 rounded-2xl shadow-custom-soft border border-gray-100 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-brand">Kursus Saya</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Lanjutkan pelatihan yang sedang berjalan atau mulai kursus baru.
                    </p>
                </div>

                <div class="w-full sm:w-auto sm:min-w-[280px]">
                    <div class="relative group">
                        <input x-model="searchQuery" type="text" placeholder="Cari judul atau kategori..."
                            class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl w-full text-sm
                                   focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all shadow-sm group-hover:border-brand/50" />
                        <i data-lucide="search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-hover:text-brand/70 transition-colors"></i>
                        
                        {{-- Clear button --}}
                        <button x-show="searchQuery.length > 0" @click="searchQuery = ''" x-cloak
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- TABS FILTER --}}
            <div class="flex flex-wrap gap-x-2 gap-y-2 border-b border-gray-200 pb-1">
                @foreach($tabs as $key => $label)
                @php $isActive = $activeTab === $key; @endphp
                <a href="{{ $tabUrl($key) }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-t-lg transition-all relative
                              {{ $isActive ? 'text-brand bg-white border-x border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)]' 
                                           : 'text-gray-500 hover:text-brand hover:bg-gray-50' }}">
                    <span>{{ $label }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold
                                     {{ $isActive ? 'bg-brand/10 text-brand'
                                                  : 'bg-gray-200 text-gray-600' }}">
                        {{ $counts[$key] ?? 0 }}
                    </span>
                    @if($isActive)
                        <div class="absolute bottom-[-1px] left-0 right-0 h-[1px] bg-white"></div>
                    @endif
                </a>
                @endforeach
            </div>
        </section>

        {{-- COURSE CARDS --}}
        <section class="min-h-[300px]">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($courses as $c)
                @php
                $status = $c['status'];
                $courseId = $c['id'];
                $detailUrl = route('user.courses.show', $courseId);

                // Logic badge & border
                $borderClass = match($status) {
                    'completed' => 'border-emerald-500',
                    'in_progress' => 'border-brand',
                    'private' => 'border-gray-400',
                    default => 'border-brand'
                };

                // Logic CTA
                $hasPreTest = !empty($c['preTest'] ?? null) && !empty($c['submit_pre_url'] ?? null);
                $hasPostTest = !empty($c['postTest'] ?? null) && !empty($c['submit_post_url'] ?? null);
                $preDone = isset($c['preTestScore']) && $c['preTestScore'] !== null;
                $postDone = isset($c['postTestScore']) && $c['postTestScore'] !== null;
                $hasReviewUrl = !empty($c['submit_review_url'] ?? null);
                @endphp

                <article x-show="matchesSearch('{{ strtolower($c['title']) }}', '{{ strtolower($c['category']) }}')"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="bg-white rounded-2xl shadow-custom-soft hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden border-t-[6px] {{ $borderClass }} flex flex-col h-full group">
                    
                    {{-- Thumbnail --}}
                    <div class="relative h-44 overflow-hidden bg-gray-100">
                        <img src="{{ $c['thumbnail'] }}" alt="{{ $c['title'] }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 {{ $status === 'private' ? 'opacity-75 grayscale' : '' }}">

                        @if($status === 'private')
                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center backdrop-blur-[1px]">
                            <div class="flex items-center gap-2 text-xs font-bold text-white bg-black/40 px-3 py-1.5 rounded-full border border-white/20">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                <span>Private / Locked</span>
                            </div>
                        </div>
                        @endif

                        <span class="absolute top-3 left-3 text-[10px] tracking-wide font-bold px-2.5 py-1 rounded-full shadow-sm
                                       {{ $status === 'completed'
                                          ? 'text-emerald-700 bg-emerald-50 border border-emerald-100'
                                          : 'text-brand bg-white/95 backdrop-blur border border-gray-100' }}">
                            {{ $c['category'] }}
                        </span>
                    </div>

                    <div class="p-5 flex flex-col flex-grow">
                        {{-- Title + Instructor --}}
                        <div class="mb-4">
                            <h3 class="font-bold text-lg text-gray-800 line-clamp-2 group-hover:text-brand transition-colors">
                                <a href="{{ $detailUrl }}" class="focus:outline-none">
                                    {{ $c['title'] }}
                                </a>
                            </h3>
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1.5">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-gray-400"></i>
                                <span>{{ $c['instructor'] }}</span>
                            </p>
                        </div>

                        {{-- Meta Info --}}
                        <div class="space-y-3 mb-4">
                            {{-- Progress Bar --}}
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-xs font-bold {{ $status === 'completed' ? 'text-emerald-600' : 'text-brand' }}">
                                        {{ $c['progress'] }}% Selesai
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium">
                                        {{ $c['done'] }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-500 {{ $status === 'completed' ? 'bg-emerald-500' : 'bg-brand' }}" 
                                         style="width: {{ $c['progress'] }}%">
                                    </div>
                                </div>
                            </div>

                            {{-- Stats --}}
                            <div class="flex flex-wrap gap-3 text-[11px] text-gray-600">
                                @if(!empty($c['total_modules']))
                                <span class="inline-flex items-center gap-1 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                                    <i data-lucide="layers" class="w-3 h-3 text-gray-400"></i>
                                    {{ $c['total_modules'] }} Modul
                                </span>
                                @endif
                                @if(!empty($c['total_duration']))
                                <span class="inline-flex items-center gap-1 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                                    <i data-lucide="clock" class="w-3 h-3 text-gray-400"></i>
                                    {{ $c['total_duration'] }}
                                </span>
                                @endif
                            </div>
                            
                            {{-- Test Scores --}}
                            @if(isset($c['preTestScore']) || isset($c['postTestScore']))
                            <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-50">
                                @if(isset($c['preTestScore']))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-[10px] font-medium text-blue-700 border border-blue-100">
                                    Pre: {{ $c['preTestScore'] }}
                                </span>
                                @endif
                                @if(isset($c['postTestScore']))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-medium text-emerald-700 border border-emerald-100">
                                    Post: {{ $c['postTestScore'] }}
                                </span>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Footer: Status & CTA --}}
                        <div class="mt-auto pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            {{-- Status Badge --}}
                            <div class="flex items-center gap-4">
                                @php
                                $statusLabel = match($status) {
                                    'in_progress' => 'Sedang Berjalan',
                                    'completed' => 'Selesai',
                                    'private' => 'Private',
                                    'expired' => 'Expired',
                                    default => ucfirst(str_replace('_', ' ', $status))
                                };
                                $statusColor = match($status) {
                                    'completed' => 'bg-emerald-50 text-emerald-700',
                                    'in_progress' => 'bg-amber-50 text-amber-700',
                                    'private' => 'bg-gray-100 text-gray-600',
                                    'not_started' => 'bg-blue-50 text-blue-700',
                                    default => 'bg-rose-50 text-rose-600'
                                };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusColor }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current @if($status === 'in_progress') animate-pulse @endif"></span>
                                    {{ $statusLabel }}
                                </span>

                                @if(!empty($c['assigned_on']))
                                <span class="text-[10px] text-gray-400" title="Tanggal Ditugaskan">
                                    {{ $c['assigned_on'] }}
                                </span>
                                @endif
                            </div>

                            {{-- CTA Buttons --}}
                            <div class="w-full sm:w-auto flex justify-end">
                                @php
                                $modules = collect($c['modules'] ?? []);
                                $allModulesDone = $modules->every(fn($m) => ($m['status'] ?? '') === 'completed');
                                @endphp

                                @if($status === 'not_started')
                                    @if($hasPreTest)
                                        <x-ui.button variant="primary" size="sm" onclick="window.TestFlow?.startPreTest('{{ $courseId }}')">
                                            Start Pre-Test
                                        </x-ui.button>
                                    @else
                                        <x-ui.button as="a" href="{{ $detailUrl }}" variant="primary" size="sm" class="w-full justify-center">
                                            Mulai Belajar
                                        </x-ui.button>
                                    @endif

                                @elseif($status === 'in_progress')
                                    @if($allModulesDone && empty($c['postTestScore']) && $modules->count() > 0 && $hasPostTest)
                                        <x-ui.button variant="primary" size="sm" onclick="window.TestFlow?.startPostTest('{{ $courseId }}')">
                                            Lanjut Post-Test
                                        </x-ui.button>
                                    @else
                                        <x-ui.button as="a" href="{{ $detailUrl }}" variant="primary" size="sm" class="w-full justify-center">
                                            Continue
                                        </x-ui.button>
                                    @endif

                                @elseif($status === 'completed')
                                    @if($c['hasReviewed'])
                                        <x-ui.button as="a" href="{{ $c['certificateUrl'] }}" target="_blank" variant="success" size="sm" class="w-full justify-center">
                                            <i data-lucide="award" class="w-3.5 h-3.5 mr-1"></i>
                                            Lihat Sertifikat
                                        </x-ui.button>
                                    @elseif($hasReviewUrl)
                                        <x-ui.button variant="primary" size="sm" onclick="window.TestFlow?.openReview('{{ $courseId }}')" class="w-full justify-center group">
                                            <i data-lucide="star" class="w-3.5 h-3.5 mr-1 group-hover:scale-110 transition-transform"></i>
                                            Review Kursus
                                        </x-ui.button>
                                    @else
                                        <x-ui.button as="a" href="{{ $detailUrl }}" variant="secondary" size="sm" class="w-full justify-center">
                                            Lihat Detail
                                        </x-ui.button>
                                    @endif

                                @elseif($status === 'private')
                                    <x-ui.button variant="subtle" size="sm" disabled class="w-full justify-center opacity-70">
                                        <i data-lucide="lock" class="w-3 h-3 mr-1"></i> Terkunci
                                    </x-ui.button>

                                @elseif($status === 'expired')
                                    <x-ui.button variant="subtle" size="sm" disabled title="Hubungi admin untuk perpanjangan" class="w-full justify-center">
                                        Expired
                                    </x-ui.button>

                                @else
                                    <x-ui.button variant="subtle" size="sm" disabled class="w-full justify-center">
                                        {{ $status }}
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
                @empty
                <div class="col-span-full py-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                        <i data-lucide="folder-open" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <h3 class="text-gray-900 font-semibold text-lg">Belum ada kursus</h3>
                    <p class="text-gray-500 text-sm mt-1 max-w-sm mx-auto">
                        Tidak ada kursus yang ditemukan dalam kategori ini. Kursus baru yang ditugaskan akan muncul di sini.
                    </p>
                </div>
                @endforelse

                {{-- Empty State for Search --}}
                <div x-show="searchQuery.length > 0 && filteredCount === 0" x-cloak class="col-span-full py-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                        <i data-lucide="search-x" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <h3 class="text-gray-900 font-semibold text-lg">Tidak ditemukan</h3>
                    <p class="text-gray-500 text-sm mt-1">
                        Tidak ada kursus yang cocok dengan pencarian "<span x-text="searchQuery" class="font-medium text-gray-700"></span>".
                    </p>
                    <button @click="searchQuery = ''" class="mt-4 text-brand hover:underline text-sm font-medium">
                        Bersihkan pencarian
                    </button>
                </div>
            </div>
        </section>
    </div>

    {{-- Test Modals --}}
    <x-test.modals :courses="$courses" />

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('courseSearch', () => ({
                searchQuery: '',
                
                init() {
                    this.$watch('searchQuery', () => {
                        this.$nextTick(() => {
                            if (window.lucide) window.lucide.createIcons();
                        });
                    });
                    
                    // Initial icon load
                    if (window.lucide) window.lucide.createIcons();
                },

                matchesSearch(title, category) {
                    if (this.searchQuery === '') return true;
                    const q = this.searchQuery.toLowerCase();
                    return title.includes(q) || category.includes(q);
                },

                get filteredCount() {
                    if (this.searchQuery === '') return this.items.length;
                    return this.items.filter(item => 
                        item.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                        item.category.toLowerCase().includes(this.searchQuery.toLowerCase())
                    ).length;
                },

                items: @json(collect($courses)->map(fn($c) => [
                    'title' => $c['title'] ?? '',
                    'category' => $c['category'] ?? ''
                ]))
            }));
        });
    </script>
    @endpush
</x-app-layout>