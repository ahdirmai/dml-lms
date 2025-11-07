{{-- resources/views/dashboard/index.blade.php --}}
<x-app-layout :title="$title ?? 'Dashboard'">
    {{-- Header --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div id="content-area" class="space-y-8">
        <!-- Header Greeting -->
        <div class="mb-8">
            @php
            $displayName = isset($userInfo['name']) && is_string($userInfo['name'])
            ? explode(' ', trim($userInfo['name']))[0]
            : (auth()->user()->name ?? 'Pengguna');
            @endphp
            <h1 class="text-3xl font-extrabold text-dark">Halo, {{ $displayName }}!</h1>
            <p class="text-lg text-gray-600">Selamat datang di Pusat Pembelajaran Karyawan. Tingkatkan kompetensi Anda
                hari ini.</p>
        </div>

        <!-- Ringkasan Performa -->
        <div class="grid grid-cols-1 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-custom-soft border border-gray-100 h-full">
                <h2 class="text-xl font-bold mb-4 text-brand">Ringkasan Performa Pelatihan</h2>
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-600">
                            Progres Keseluruhan Pelatihan ({{ $performance['overallProgress'] ?? 0 }}%)
                        </span>
                        <span class="text-sm font-semibold text-brand">
                            {{ $performance['completed'] ?? 0 }} / {{ $performance['total'] ?? 0 }} Kelas Selesai
                        </span>
                    </div>
                    <div class="w-full bg-soft rounded-full h-2.5">
                        <div class="h-2.5 rounded-full {{ ($performance['overallProgress'] ?? 0) === 100 ? 'bg-green-500' : 'bg-brand' }}"
                            style="width:{{ $performance['overallProgress'] ?? 0 }}%"></div>
                    </div>
                </div>

                {{-- Stat boxes --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div class="p-4 bg-green-50 rounded-xl shadow-sm border border-green-200">
                        <x-ui.icon name="check-circle" class="w-7 h-7 mx-auto mb-1 text-green-600" />
                        <div class="text-3xl font-bold text-dark">{{ $performance['completed'] ?? 0 }}</div>
                        <p class="text-xs text-gray-600 font-medium mt-1">Kelas Selesai</p>
                    </div>
                    <div class="p-4 bg-accent/10 rounded-xl shadow-sm border border-accent/20">
                        <x-ui.icon name="clock" class="w-7 h-7 mx-auto mb-1 text-accent" />
                        <div class="text-3xl font-bold text-dark">{{ $performance['inProgress'] ?? 0 }}</div>
                        <p class="text-xs text-gray-600 font-medium mt-1">Sedang Berjalan</p>
                    </div>
                    <div class="p-4 bg-brand/10 rounded-xl shadow-sm border border-brand/20">
                        <x-ui.icon name="package" class="w-7 h-7 mx-auto mb-1 text-brand" />
                        <div class="text-3xl font-bold text-dark">{{ $performance['total'] ?? 0 }}</div>
                        <p class="text-xs text-gray-600 font-medium mt-1">Total Kelas Ditugaskan</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Ringkasan Performa -->

        <!-- Leaderboard & AI Assistant -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            {{-- Leaderboard --}}
            <div class="lg:col-span-2 flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                @php
                $getTopThree = function($data) {
                $data = array_values($data ?? []);
                return [
                1 => $data[1] ?? null, // Rank 1
                2 => $data[0] ?? null, // Rank 2
                3 => $data[2] ?? null, // Rank 3
                ];
                };
                $topThreeCompleted = $getTopThree($leaderboardData['completedCourses'] ?? []);
                $currentUserCompleted = collect($leaderboardData['completedCourses'] ?? [])->firstWhere('isYou', true);

                $topThreePostTest = $getTopThree($leaderboardData['postTest'] ?? []);
                $currentUserPostTest = collect($leaderboardData['postTest'] ?? [])->firstWhere('isYou', true);
                @endphp

                <!-- Pelatihan Terbanyak -->
                <div
                    class="bg-white p-4 rounded-2xl shadow-custom-soft border border-gray-100 flex flex-col md:w-1/2 lg:w-1/2">
                    <div class="subcard-header-count rounded-xl p-3 text-center text-lg font-bold">Pelatihan Terbanyak
                    </div>
                    <div class="p-3 pt-6 flex justify-around space-x-2">
                        @foreach($topThreeCompleted as $rank => $item)
                        @if($item)
                        @php
                        $rankOrderClass = $rank === 2 ? 'order-1' : ($rank === 1 ? 'order-2' : 'order-3');
                        $scoreClass = $rank === 1 ? 'score-rank-1' : ($rank === 2 ? 'score-rank-2' : 'score-rank-3');
                        $nameDisplay = explode(' ', $item['name'])[0] ?? 'User';
                        @endphp
                        <div class="flex flex-col items-center w-1/3 text-center p-2 {{ $rankOrderClass }}">
                            <i data-lucide="rocket" class="w-8 h-8 text-accent"></i>
                            <p class="font-medium text-xs text-gray-700">{{ $nameDisplay }}</p>
                            <div class="text-[0.6rem] font-medium text-accent font-bold">{{ $item['count'] }} Pelatihan
                            </div>
                            <div class="{{ $scoreClass }} score-box font-extrabold mt-3">{{ $rank }}</div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @if($currentUserCompleted)
                    <div class="text-sm font-semibold text-gray-500 mt-4 mb-2 border-t pt-3 px-3">Peringkat Anda</div>
                    <div
                        class="highlight-you rounded-lg flex justify-between items-center px-3 py-2 text-gray-800 border border-accent/30">
                        <div class="flex items-center space-x-4">
                            <span class="text-3xl font-extrabold text-accent w-10 text-center">{{
                                $currentUserCompleted['rank'] }}</span>
                            <div class="text-left">
                                <p class="font-bold text-sm text-accent">{{ $currentUserCompleted['name'] }}</p>
                                <p class="text-xs text-gray-600">{{ $userInfo['position'] ?? '' }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-gray-700">{{ $currentUserCompleted['count'] }}
                            Pelatihan</span>
                    </div>
                    @endif
                </div>

                <!-- Nilai Post Test Tertinggi -->
                <div
                    class="bg-white p-4 rounded-2xl shadow-custom-soft border border-gray-100 flex flex-col md:w-1/2 lg:w-1/2">
                    <div class="subcard-header-score rounded-xl p-3 text-center text-lg font-bold">Nilai Post Test
                        Tertinggi</div>
                    <div class="p-3 pt-6 flex justify-around space-x-2">
                        @foreach($topThreePostTest as $rank => $item)
                        @if($item)
                        @php
                        $rankOrderClass = $rank === 2 ? 'order-1' : ($rank === 1 ? 'order-2' : 'order-3');
                        $scoreClass = $rank === 1 ? 'score-rank-1' : ($rank === 2 ? 'score-rank-2' : 'score-rank-3');
                        $nameDisplay = explode(' ', $item['name'])[0] ?? 'User';
                        @endphp
                        <div class="flex flex-col items-center w-1/3 text-center p-2 {{ $rankOrderClass }}">
                            <i data-lucide="trophy" class="w-8 h-8 text-brand"></i>
                            <p class="font-medium text-xs text-gray-700">{{ $nameDisplay }}</p>
                            <div class="text-[0.6rem] font-medium text-gray-500">{{ $item['score'] }}</div>
                            <div class="{{ $scoreClass }} score-box font-extrabold mt-3">{{ $rank }}</div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @if($currentUserPostTest)
                    <div class="text-sm font-semibold text-gray-500 mt-4 mb-2 border-t pt-3 px-3">Peringkat Anda</div>
                    <div
                        class="highlight-you rounded-lg flex justify-between items-center px-3 py-2 text-gray-800 border border-accent/30">
                        <div class="flex items-center space-x-4">
                            <span class="text-3xl font-extrabold text-accent w-10 text-center">{{
                                $currentUserPostTest['rank'] }}</span>
                            <div class="text-left">
                                <p class="font-bold text-sm text-accent">{{ $currentUserPostTest['name'] }}</p>
                                <p class="text-xs text-gray-600">{{ $userInfo['position'] ?? '' }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-gray-700">{{ $currentUserPostTest['score'] }}.0</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- AI Assistant (statis) -->
            <div class="lg:col-span-1">
                <div
                    class="bg-white p-6 rounded-2xl shadow-custom-soft border border-gray-100 h-full flex flex-col lg:col-span-1">
                    <h2 class="text-xl font-bold text-brand flex items-center mb-4">
                        <i data-lucide="sparkles" class="w-6 h-6 mr-2 text-accent"></i> AI Learning Assistant
                    </h2>
                    <p class="text-sm text-gray-700 mb-4">Tanyakan ringkasan kursus, istilah sulit, atau minta
                        rekomendasi pelatihan lanjutan.</p>
                    <div class="flex-grow flex flex-col justify-end">
                        <input type="text"
                            class="w-full p-3 mb-3 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent"
                            placeholder="Contoh: Apa itu B3?" />
                        <button class="w-full cta-button cta-review flex items-center justify-center">
                            <i data-lucide="send" class="w-4 h-4 mr-2"></i> Tanya AI
                        </button>
                        <p class="text-xs text-gray-500 mt-4 italic text-center">
                            "AI ini dapat membantu Anda memahami materi lebih cepat."
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Leaderboard & AI -->

        <!-- Daftar Kelas -->
        <div class="space-y-8">
            <div class="bg-white p-6 rounded-2xl shadow-custom-soft border border-gray-100">
                <h2 class="text-xl font-bold mb-4 text-brand">Semua Kelas (<span id="filter-status">All</span>)</h2>

                {{-- Filter buttons --}}
                <div id="filter-buttons" class="flex space-x-2 md:space-x-4 mb-8 overflow-x-auto scrollbar-hide">
                    @foreach(['All','Completed','In Progress','Not Started','Expired'] as $filter)
                    <button onclick="filterCourses(event, '{{ $filter }}')"
                        class="filter-btn flex-shrink-0 px-4 py-2 text-sm font-medium rounded-full transition-colors duration-200 {{ $filter === 'All' ? 'bg-brand text-white shadow-md' : 'bg-white text-gray-700 hover:bg-soft border border-gray-200' }}"
                        data-filter="{{ $filter }}">
                        {{ $filter }}
                    </button>
                    @endforeach
                </div>

                {{-- Course list --}}
                <div id="course-list-container" class="space-y-6">
                    @forelse($courses as $course)
                    @php
                    $courseId = $course['id'];
                    $detailUrl = route('user.courses.show', $courseId);
                    @endphp
                    <div class="course-card bg-white p-4 rounded-xl shadow-custom-soft border border-gray-100 flex flex-col md:flex-row transition transform hover:scale-[1.01] duration-300 hover:shadow-lg"
                        data-status="{{ $course['status'] }}" style="display: flex;">
                        <div
                            class="w-full md:w-56 h-36 bg-soft rounded-lg overflow-hidden flex-shrink-0 mb-4 md:mb-0 md:mr-6 relative">
                            <img src="https://placehold.co/400x300/09759A/FFFFFF?text=LMS&font=inter"
                                alt="{{ $course['title'] }}" class="w-full h-full object-cover">
                            <div class="absolute top-2 left-2 badge bg-brand text-white font-semibold">
                                {{ $course['category'] }}
                            </div>
                        </div>

                        <div class="flex-grow flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 hover:text-brand transition-colors">
                                    {{-- Link ke detail kursus --}}
                                    <a href="{{ $detailUrl }}">{{ $course['title'] }}</a>
                                </h3>
                                <p class="text-sm text-gray-500 mb-3">{{ $course['subtitle'] }}</p>

                                <div class="flex flex-wrap items-center text-xs text-gray-600 gap-x-4 gap-y-1 mb-3">
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

                                <div class="flex items-center space-x-4 mb-4">
                                    <div class="text-sm font-medium">
                                        Pre-Test:
                                        <span
                                            class="{{ isset($course['preTestScore']) && $course['preTestScore'] !== null ? 'font-bold text-green-600' : 'italic text-gray-400' }}">
                                            {{ $course['preTestScore'] ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="text-sm font-medium">
                                        Post-Test:
                                        <span
                                            class="{{ isset($course['postTestScore']) && $course['postTestScore'] !== null ? 'font-bold text-green-600' : 'italic text-gray-400' }}">
                                            {{ $course['postTestScore'] ?? '-' }}
                                        </span>
                                    </div>

                                    {{-- Status badge --}}
                                    @switch($course['status'])
                                    @case('Completed')
                                    <span class="badge badge-completed"><i data-lucide="check-circle"
                                            class="w-3 h-3 mr-1"></i> Completed</span>
                                    @break
                                    @case('In Progress')
                                    <span class="badge badge-in-progress"><i data-lucide="loader"
                                            class="w-3 h-3 mr-1 animate-spin"></i> In Progress</span>
                                    @break
                                    @case('Not Started')
                                    <span class="badge badge-not-started"><i data-lucide="circle-dot"
                                            class="w-3 h-3 mr-1"></i> Not Started</span>
                                    @break
                                    @case('Expired')
                                    <span class="badge badge-expired"><i data-lucide="alert-triangle"
                                            class="w-3 h-3 mr-1"></i> Expired</span>
                                    @break
                                    @default
                                    <span class="badge">{{ $course['status'] }}</span>
                                    @endswitch
                                </div>
                            </div>

                            <div
                                class="mt-4 md:mt-0 pt-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                                <div class="flex-grow w-full sm:w-auto sm:mr-6">
                                    <p class="text-xs font-medium mb-1 text-gray-600">Progress: {{ $course['progress']
                                        }}%</p>
                                    <div class="w-full bg-light rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full {{ $course['progress'] === 100 ? 'bg-green-500' : 'bg-brand' }}"
                                            style="width:{{ $course['progress'] }}%"></div>
                                    </div>
                                </div>
                                <div class="w-full sm:w-auto">
                                    {{-- CTA --}}
                                    @if($course['status'] === 'Not Started')
                                    <button class="cta-button cta-start" onclick="startPreTest('{{ $courseId }}')">
                                        Start Pre-Test
                                    </button>
                                    @elseif($course['status'] === 'In Progress')
                                    @php
                                    $modules = collect($course['modules'] ?? []);
                                    $allModulesDone = $modules->every(fn($m) => ($m['status'] ?? '') === 'completed');
                                    @endphp
                                    @if($allModulesDone && empty($course['postTestScore']) && $modules->count() > 0)
                                    <button class="cta-button cta-start" onclick="startPostTest('{{ $courseId }}')">
                                        Lanjut Post-Test
                                    </button>
                                    @else
                                    {{-- Continue ke detail kursus --}}
                                    <a href="{{ $detailUrl }}" class="cta-button cta-start inline-block">Continue</a>
                                    @endif
                                    @elseif($course['status'] === 'Completed')
                                    <button class="cta-button cta-review"
                                        onclick="openGenericModal('Review Course','Anda telah menyelesaikan kursus ini. Lihat sertifikat atau ulasan.')">
                                        Review
                                    </button>
                                    @elseif($course['status'] === 'Expired')
                                    <button class="cta-button cta-disabled" disabled
                                        title="Hubungi admin untuk perpanjangan">
                                        Expired
                                    </button>
                                    @else
                                    <button class="cta-button cta-disabled" disabled>Status: {{ $course['status']
                                        }}</button>
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
        </div>
        <!-- END Daftar Kelas -->
    </div>

    {{-- MODALS --}}
    <!-- Modal konfirmasi Post-Test -->
    <div id="modal-posttest-confirm"
        class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden items-center justify-center p-4">
        <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full">
            <h3 id="posttest-confirm-title" class="text-xl font-bold mb-3 text-brand">Modul Selesai!</h3>
            <p id="posttest-confirm-message" class="text-gray-700 mb-4">Anda telah menyelesaikan semua modul untuk
                kursus ini. Lanjutkan ke Post-Test sekarang?</p>
            <div class="flex justify-end space-x-2">
                <a href="#" id="posttest-confirm-later" class="cta-button cta-review"
                    onclick="event.preventDefault(); closePostTestConfirmModal();">Nanti Saja</a>
                <button onclick="confirmStartPostTest();" class="cta-button cta-start">Mulai Post-Test</button>
            </div>
        </div>
    </div>

    <!-- Generic Modal -->
    <div id="modal-generic" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden items-center justify-center p-4">
        <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full">
            <h3 id="modal-title" class="text-xl font-bold mb-3 text-brand"></h3>
            <p id="modal-message" class="text-gray-700 mb-4"></p>
            <div class="flex justify-end">
                <button onclick="closeGenericModal()" class="w-full cta-button cta-review">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Test Modal (Pre/Post) -->
    <div id="modal-test" class="fixed inset-0 hidden items-center justify-center z-50 bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 overflow-auto max-h-[90vh]">
            <h3 id="test-title" class="text-xl font-bold text-brand mb-2"></h3>
            <div id="test-desc" class="text-sm text-gray-600 mb-4"></div>
            <form id="test-form" class="space-y-4"></form>
            <div class="flex justify-between items-center mt-4">
                <div id="test-progress" class="text-sm text-gray-500"></div>
                <div class="flex space-x-2">
                    <button onclick="closeTestModal()" class="cta-button cta-review" type="button">Batal</button>
                    <button id="test-submit" class="cta-button cta-start" type="button">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="modal-review" class="fixed inset-0 hidden items-center justify-center z-50 bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-brand mb-2">Berikan Ulasan</h3>
            <p class="text-sm text-gray-600 mb-4">Berikan rating 1–5 bintang untuk kursus ini.</p>
            <div id="review-stars" class="flex items-center justify-center space-x-2 text-yellow-400 text-2xl mb-4">
            </div>
            <div class="text-center">
                <button id="review-submit" class="cta-button cta-start">Kirim Ulasan</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed right-6 bottom-6 hidden bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg z-[150]">
    </div>

    {{-- STYLES --}}
    @push('styles')
    <style>
        .cta-button {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .cta-button:active:not(.disabled) {
            transform: scale(0.98);
            box-shadow: none;
        }

        .cta-start {
            background-color: var(--color-secondary);
            color: #fff;
        }

        .cta-start:hover:not(.disabled) {
            filter: brightness(0.95);
        }

        .cta-review {
            background-color: var(--color-primary);
            color: #fff;
        }

        .cta-review:hover:not(.disabled) {
            filter: brightness(0.95);
        }

        .cta-disabled {
            background-color: var(--color-light);
            color: #9ca3af;
            cursor: not-allowed;
        }

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

        .subcard-header-score {
            background-color: var(--color-green-light-prd);
            color: #1f2937;
        }

        .subcard-header-count {
            background-color: var(--color-red-light-prd);
            color: #1f2937;
        }

        .score-box {
            width: 100%;
            padding: 0.4rem 0.15rem;
            border-radius: 0.5rem;
            font-size: 1.25rem;
            font-weight: 800;
            text-align: center;
            margin-top: 0.5rem;
        }

        .score-rank-1 {
            background-color: var(--color-medal-gold);
            color: #333;
        }

        .score-rank-2 {
            background-color: var(--color-medal-silver);
            color: #333;
        }

        .score-rank-3 {
            background-color: var(--color-medal-bronze);
            color: #fff;
        }

        /* FIX: hilangkan karakter tak valid dan beri background lembut */
        .highlight-you {
            background-color: rgba(13, 148, 136, 0.10);
            /* approx teal 10% */
            color: var(--color-secondary);
            font-weight: 700;
        }

        .star {
            cursor: pointer;
            font-size: 1.6rem;
            color: #F6C700;
        }
    </style>
    @endpush

    {{-- SCRIPTS --}}
    @push('scripts')
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // STORAGE KEY
        const STORAGE_KEY = 'dml_lms_state_v1';

        // === DATA DARI CONTROLLER ===
        const USER_INFO = @json($userInfo ?? []);
        const DEFAULT_COURSES = @json($courses ?? []);
        const LEADERBOARD_DATA = @json($leaderboardData ?? []);
        const PERFORMANCE_STATS = @json($performance ?? []);
        // ============================

        // STATE
        let state = { courses: [] };
        let activePostTestCourseId = null;

        // Toast
        function showToast(text, timeout=2200){
            const t = document.getElementById('toast');
            if(!t) return;
            t.textContent = text;
            t.classList.remove('hidden');
            setTimeout(()=> t.classList.add('hidden'), timeout);
        }

        // Generic modal
        function openGenericModal(title, message){
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-message').textContent = message;
            const modal = document.getElementById('modal-generic');
            modal.classList.remove('hidden'); modal.classList.add('flex');
        }
        function closeGenericModal(){
            const modal = document.getElementById('modal-generic');
            modal.classList.add('hidden'); modal.classList.remove('flex');
        }

        // Post Test Confirm
        function openPostTestConfirmModal(courseId, title){
            activePostTestCourseId = courseId;
            document.getElementById('posttest-confirm-title').textContent = `Modul Selesai: ${title}`;
            const laterBtn = document.getElementById('posttest-confirm-later');
            if(laterBtn) laterBtn.href = `{{ url('/courses') }}/${courseId}`;
            const m = document.getElementById('modal-posttest-confirm'); m.classList.remove('hidden'); m.classList.add('flex');
        }
        function closePostTestConfirmModal(){
            const m = document.getElementById('modal-posttest-confirm'); m.classList.add('hidden'); m.classList.remove('flex');
            activePostTestCourseId = null;
        }
        function confirmStartPostTest(){
            if (activePostTestCourseId) { closePostTestConfirmModal(); startPostTest(activePostTestCourseId); }
        }

        // Test modal
        let activeTestContext = null; // { courseId, type }

        function openTestModal(courseId, type){
            const course = getCourse(courseId); if (!course) return;
            activeTestContext = { courseId, type };
            document.getElementById('test-title').textContent = (type === 'pre' ? 'Pre-Test: ' : 'Post-Test: ') + (course.title || 'Kursus');
            document.getElementById('test-desc').textContent = type === 'pre'
                ? 'Jawablah soal berikut. Skor pre-test akan dicatat.'
                : 'Post-Test — tes akhir setelah menonton semua modul.';
            const form = document.getElementById('test-form'); form.innerHTML = '';

            const items = type === 'pre' ? (course.preTest || []) : (course.postTest || []);
            if (!items.length) {
                form.innerHTML = "<p class='text-gray-500'>Soal untuk tes ini tidak ditemukan.</p>";
                document.getElementById('test-submit').style.display = 'none';
                document.getElementById('test-progress').textContent = '0 soal';
            } else {
                items.forEach((it, idx) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'p-3 border rounded-md bg-gray-50';
                    wrapper.innerHTML = `
                        <p class="font-medium text-gray-800">${idx+1}. ${it.q}</p>
                        <div class="mt-2 space-y-2" id="q-${idx}">
                            ${it.options.map((opt,oIdx)=> `<label class="flex items-center"><input type="radio" name="q${idx}" value="${oIdx}" class="mr-2 text-brand focus:ring-accent"> <span class="text-sm text-gray-700">${opt}</span></label>`).join('')}
                        </div>
                    `;
                    form.appendChild(wrapper);
                });
                document.getElementById('test-progress').textContent = `${items.length} soal`;
                document.getElementById('test-submit').style.display = 'block';
            }

            document.getElementById('test-submit').onclick = () => submitTest();
            const m = document.getElementById('modal-test'); m.classList.remove('hidden'); m.classList.add('flex');
        }
        function closeTestModal(){
            const m = document.getElementById('modal-test'); m.classList.add('hidden'); m.classList.remove('flex');
            activeTestContext = null;
        }

        function submitTest(){
            if (!activeTestContext) return;
            const { courseId, type } = activeTestContext;
            const course = getCourse(courseId); if (!course) return;
            const items = type === 'pre' ? (course.preTest || []) : (course.postTest || []);

            let correct = 0, score = 0;
            if(items.length) {
                for (let i=0;i<items.length;i++){
                    const radios = document.getElementsByName(`q${i}`);
                    let selected = null;
                    for (const r of radios) if (r.checked) { selected = Number(r.value); break; }
                    if (selected !== null && selected === items[i].answer) correct++;
                }
                score = Math.round((correct / items.length) * 100);
            } else {
                score = 100;
            }

            if (type === 'pre'){
                course.preTestScore = score;
                (course.modules || []).forEach((m, idx) => { m.status = idx === 0 ? 'in-progress' : (m.status === 'completed' ? 'completed' : 'locked'); });
                course.status = 'In Progress';
                updateCourseProgress(course);
                saveState();
                closeTestModal();
                window.location.href = `{{ url('/courses') }}/${course.id}`;
            } else {
                course.postTestScore = score;
                updateCourseProgress(course);
                saveState();
                closeTestModal();
                openReviewModal(courseId);
            }
        }

        // Review modal
        let activeReviewCourseId = null;
        function openReviewModal(courseId){
            activeReviewCourseId = courseId;
            const container = document.getElementById('review-stars');
            container.innerHTML = '';
            for (let i=1;i<=5;i++){
                const el = document.createElement('span');
                el.className = 'star';
                el.dataset.value = i;
                el.innerHTML = '☆';
                el.onclick = () => highlightStars(i);
                container.appendChild(el);
            }
            highlightStars(5);
            const m = document.getElementById('modal-review'); m.classList.remove('hidden'); m.classList.add('flex');
            document.getElementById('review-submit').onclick = () => submitReview();
        }
        function highlightStars(n){
            const container = document.getElementById('review-stars');
            [...container.children].forEach((s, idx) => s.innerHTML = (idx < n) ? '★' : '☆');
            container.dataset.chosen = n;
        }
        function submitReview(){
            const n = Number(document.getElementById('review-stars').dataset.chosen || 5);
            if (!activeReviewCourseId) return;
            const c = getCourse(activeReviewCourseId);
            c.review = { stars: n, at: new Date().toISOString() };
            c.status = 'Completed';
            (c.modules || []).forEach(m => { if (m.status !== 'completed') m.status = 'completed'; });
            updateCourseProgress(c);
            saveState();
            closeReviewModal();
            showToast('Terima kasih atas ulasan Anda!');
            window.location.href = `{{ url('/courses') }}/${c.id}?review=success`;
        }
        function closeReviewModal(){
            const m = document.getElementById('modal-review'); m.classList.add('hidden'); m.classList.remove('flex');
            activeReviewCourseId = null;
        }

        // Storage
        function loadState(){
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw){
                try {
                    state = JSON.parse(raw) || {};
                    if (!Array.isArray(state.courses) || state.courses.length !== (DEFAULT_COURSES || []).length) {
                        state = { courses: JSON.parse(JSON.stringify(DEFAULT_COURSES || [])) };
                    }
                } catch(e){ state = { courses: JSON.parse(JSON.stringify(DEFAULT_COURSES || [])) }; }
            } else {
                state = { courses: JSON.parse(JSON.stringify(DEFAULT_COURSES || [])) };
            }
            state.courses.forEach(c => {
                if (!Array.isArray(c.modules)) c.modules = [];
                if (c.status === 'Not Started'){ c.modules.forEach(m => m.status = 'locked'); }
            });
        }
        function saveState(){ try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch(e){} }
        function getCourse(id){ return (state.courses || []).find(c => c.id === id); }

        function updateCourseProgress(course){
            if (!course) return;
            const total = (course.modules || []).length;
            const completedCount = (course.modules || []).filter(m => m.status === 'completed').length;
            const inprogress = (course.modules || []).some(m => m.status === 'in-progress');

            const moduleProgress = total > 0 ? (completedCount / total) * 90 : (course.preTestScore != null ? 90 : 0);
            const postTestProgress = course.postTestScore != null ? 10 : 0;
            course.progress = Math.round(moduleProgress + postTestProgress);

            if (course.status === 'Expired'){
                // tetap expired
            } else if (course.postTestScore != null && (total === 0 || completedCount === total)){
                course.status = 'Completed';
            } else if (completedCount > 0 || inprogress || (course.preTestScore != null)){
                course.status = 'In Progress';
            } else {
                course.status = 'Not Started';
            }
        }

        // Filter
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
                lucide.createIcons();
            } else if (noResultEl) {
                noResultEl.remove();
            }

            if (noCoursesPlaceholder) {
                noCoursesPlaceholder.style.display = (hasResults || filter !== 'All') ? 'none' : 'block';
            }
        }

        // Actions
        function startPreTest(courseId){ openTestModal(courseId, 'pre'); }
        function startPostTest(courseId){
            const course = getCourse(courseId);
            if (!course) return;
            const allDone = (course.modules || []).every(m => m.status === 'completed');
            if (!allDone && (course.modules || []).length > 0) {
                openGenericModal('Post-Test Terkunci', 'Selesaikan semua modul terlebih dahulu untuk membuka Post-Test.');
                return;
            }
            openTestModal(courseId, 'post');
        }

        // INIT
        function initApp(){
            loadState();
            saveState();
            lucide.createIcons();
            // Pastikan filter awal selalu 'All'
            filterCourses(null, 'All');
        }

        // Close modal on ESC / overlay click
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeTestModal(); closeGenericModal(); closeReviewModal(); closePostTestConfirmModal();
            }
        });
        document.getElementById('modal-posttest-confirm').addEventListener('click', e => { if (e.target === e.currentTarget) closePostTestConfirmModal(); });
        document.getElementById('modal-test').addEventListener('click', e => { if (e.target === e.currentTarget) closeTestModal(); });
        document.getElementById('modal-generic').addEventListener('click', e => { if (e.target === e.currentTarget) closeGenericModal(); });
        document.getElementById('modal-review').addEventListener('click', e => { if (e.target === e.currentTarget) closeReviewModal(); });

        document.addEventListener('DOMContentLoaded', initApp);
    </script>
    @endpush
</x-app-layout>
