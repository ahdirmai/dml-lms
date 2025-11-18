{{-- resources/views/user/course/index.blade.php --}}
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

    {{-- HEADER: Search + info --}}
    <section class="mb-6 space-y-4">
        <div
            class="bg-white p-4 sm:p-5 rounded-2xl shadow-custom-soft border border-gray-100 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-dark">Kursus Saya</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Lanjutkan pelatihan yang sedang berjalan atau mulai kursus baru yang telah ditugaskan.
                </p>
            </div>

            <div class="w-full sm:w-auto sm:min-w-[260px]">
                <div class="relative">
                    <input id="course-search" type="text" placeholder="Cari judul atau kategori..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl w-full text-sm
                               focus:outline-none focus:ring-2 focus:ring-brand/60 focus:border-brand/40 transition" />
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- TABS FILTER --}}
        <div class="flex flex-wrap gap-x-4 gap-y-2 border-b border-gray-200 pb-2">
            @foreach($tabs as $key => $label)
            @php $isActive = $activeTab === $key; @endphp
            <a href="{{ $tabUrl($key) }}" class="inline-flex items-center gap-2 pb-2 text-sm font-semibold transition
                          {{ $isActive ? 'text-brand border-b-2 border-brand'
                                       : 'text-gray-500 hover:text-brand' }}">
                <span>{{ $label }}</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full
                                 {{ $isActive ? 'bg-brand/10 text-brand'
                                              : 'bg-gray-100 text-gray-500' }}">
                    {{ $counts[$key] ?? 0 }}
                </span>
            </a>
            @endforeach
        </div>
    </section>

    {{-- COURSE CARDS --}}
    <section>
        <div id="course-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($courses as $c)
            @php
            $status = $c['status']; // 'in_progress', 'completed', 'private', dll
            $courseId = $c['id'];

            $barColor = [
            'primary' => 'bg-brand',
            'success' => 'bg-emerald-500',
            'muted' => 'bg-gray-500',
            ][$c['cta_kind']] ?? 'bg-brand';

            $borderTop = [
            'in_progress' => 'border-brand',
            'completed' => 'border-emerald-500',
            'private' => 'border-gray-400',
            ][$status] ?? 'border-brand';

            // Untuk integrasi test modal
            $hasPreTest = !empty($c['preTest'] ?? null) && !empty($c['submit_pre_url'] ?? null);
            $hasPostTest = !empty($c['postTest'] ?? null) && !empty($c['submit_post_url'] ?? null);
            $preDone = isset($c['preTestScore']) && $c['preTestScore'] !== null;
            $postDone = isset($c['postTestScore']) && $c['postTestScore'] !== null;
            $hasReviewUrl = !empty($c['submit_review_url'] ?? null);

            $titleLower = strtolower($c['title'] ?? '');
            $categoryLower = strtolower($c['category'] ?? '');
            @endphp

            <article
                class="course-card bg-white rounded-2xl shadow-custom-soft hover:shadow-xl transition transform hover:-translate-y-1 overflow-hidden border-t-8 {{ $borderTop }}"
                data-title="{{ $titleLower }}" data-category="{{ $categoryLower }}">
                {{-- Thumbnail --}}
                <div class="relative">
                    <img src="{{ $c['thumbnail'] }}" alt="Thumbnail"
                        class="w-full h-40 object-cover {{ $status === 'private' ? 'opacity-75' : '' }}">

                    @if($status === 'private')
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                        <div class="flex items-center gap-2 text-xs font-semibold text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 11c.828 0 1.5.672 1.5 1.5S12.828 14 12 14s-1.5-.672-1.5-1.5S11.172 11 12 11z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8V7a5 5 0 10-10 0v1M5 9h14v10H5z" />
                            </svg>
                            <span>Private / Locked</span>
                        </div>
                    </div>
                    @endif

                    {{-- Kategori badge di atas thumbnail --}}
                    <span class="absolute top-3 left-3 text-[10px] tracking-wide font-bold px-2 py-1 rounded-full
                                   {{ $status === 'completed'
                                      ? 'text-emerald-700 bg-emerald-50'
                                      : 'text-brand bg-white/90 backdrop-blur' }}">
                        {{ $c['category'] }}
                    </span>
                </div>

                <div class="p-5 flex flex-col h-full">
                    {{-- Title + instructor --}}
                    <div class="
                    ">
                        <h3 class="mt-1 font-bold text-lg text-gray-800 line-clamp-2">
                            {{ $c['title'] }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Oleh: {{ $c['instructor'] }}
                        </p>

                        {{-- Meta info: progress + modul + durasi --}}
                        <div class="mt-3 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold
                                        {{ $status === 'completed' ? 'text-emerald-600' : 'text-brand' }}">
                                    {{ $c['progress'] }}% Selesai
                                </span>
                                <span class="text-[11px] text-gray-500">
                                    {{ $c['done'] }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="{{ $barColor }} h-2.5 rounded-full" style="width: {{ $c['progress'] }}%">
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3 text-[11px] text-gray-600 mt-1">
                                @if(!empty($c['total_modules']))
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="layers" class="w-3.5 h-3.5 text-gray-400"></i>
                                    {{ $c['total_modules'] }} modul
                                </span>
                                @endif
                                @if(!empty($c['total_duration']))
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-gray-400"></i>
                                    {{ $c['total_duration'] }} menit
                                </span>
                                @endif

                                {{-- Pre/Post score badge (jika ada) --}}
                                @if(isset($c['preTestScore']))
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-[10px] text-blue-700">
                                    Pre: {{ $c['preTestScore'] ?? '-' }}
                                </span>
                                @endif
                                @if(isset($c['postTestScore']))
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] text-emerald-700">
                                    Post: {{ $c['postTestScore'] ?? '-' }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Status badge & CTA --}}
                    <div class="mt-4 pt-3 border-t border-gray-100 space-y-3">
                        <div class="flex justify-between items-center text-[11px]">
                            @php
                            $statusLabel = [
                            'in_progress' => 'Sedang Berjalan',
                            'completed' => 'Selesai',
                            'private' => 'Private',
                            'expired' => 'Expired',
                            ][$status] ?? ucfirst(str_replace('_', ' ', $status));
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                                             text-[10px] font-semibold
                                             @switch($status)
                                                 @case('completed') bg-emerald-50 text-emerald-700 @break
                                                 @case('in_progress') bg-brand/10 text-brand @break
                                                 @case('private') bg-gray-100 text-gray-600 @break
                                                 @default bg-rose-50 text-rose-600
                                             @endswitch">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ $statusLabel }}
                            </span>

                            @if(!empty($c['assigned_on']))
                            <span class="text-gray-400">Ditugaskan: {{ $c['assigned_on'] }}</span>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            {{-- CTA utama (Pre/Post/Continue/Review) --}}
                            <div class="w-full sm:w-auto">
                                @if($status === 'in_progress')
                                @if($hasPreTest && !$preDone)
                                <x-ui.button variant="primary" class="w-full"
                                    onclick="window.TestFlow?.startPreTest('{{ $courseId }}')">
                                    Mulai Pre-Test
                                </x-ui.button>
                                @elseif($hasPostTest && $preDone && !$postDone)
                                <x-ui.button variant="primary" class="w-full"
                                    onclick="window.TestFlow?.startPostTest('{{ $courseId }}')">
                                    Lanjut Post-Test
                                </x-ui.button>
                                @else
                                <x-ui.button as="a" href="{{ route('user.courses.show', $courseId) }}" variant="primary"
                                    class="w-full">
                                    Lanjutkan Belajar
                                </x-ui.button>
                                @endif

                                @elseif($status === 'completed')
                                @if($hasReviewUrl)
                                <x-ui.button variant="secondary" class="w-full"
                                    onclick="window.TestFlow?.openReview('{{ $courseId }}')">
                                    Review Kursus
                                </x-ui.button>
                                @else
                                <x-ui.button as="a" href="{{ route('user.courses.show', $courseId) }}"
                                    variant="secondary" class="w-full">
                                    Lihat Detail
                                </x-ui.button>
                                @endif

                                @elseif($status === 'private')
                                <x-ui.button as="button" variant="subtle" disabled class="w-full"
                                    title="Hubungi admin untuk akses kursus ini">
                                    Terkunci
                                </x-ui.button>

                                @else
                                {{-- default: gunakan CTA bawaan --}}
                                <x-ui.button as="a" href="{{ route('user.courses.show', $courseId) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl font-bold text-white shadow
                                                            {{ $c['cta_kind']==='success' ? 'bg-emerald-600 hover:bg-emerald-700'
                                                                 : ($c['cta_kind']==='muted' ? 'bg-gray-600 hover:bg-gray-700'
                                                                                             : 'bg-brand hover:brightness-95') }}">
                                    {{ $c['cta'] }}
                                </x-ui.button>
                                @endif
                            </div>

                            {{-- Tombol kecil ke detail (opsional) --}}
                            <a href="{{ route('user.courses.show', $courseId) }}"
                                class="text-[11px] text-gray-500 hover:text-brand inline-flex items-center gap-1">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                Detail kursus
                            </a>
                        </div>
                    </div>
                </div>
            </article>
            @empty
            <div class="col-span-full">
                <div class="bg-white rounded-2xl p-10 text-center border border-dashed border-gray-300">
                    <i data-lucide="folder-open" class="w-10 h-10 mx-auto mb-3 text-gray-400"></i>
                    <p class="text-gray-700 font-semibold">Belum ada kursus pada kategori ini.</p>
                    <p class="text-sm text-gray-500 mt-1">
                        Kursus baru yang ditugaskan ke Anda akan muncul di sini.
                    </p>
                </div>
            </div>
            @endforelse
        </div>
    </section>

    {{-- KOMONEN TEST MODAL (PRE/POST/REVIEW) --}}
    {{-- Pastikan $courses yang dikirim ke sini sudah memuat:
    - preTest, postTest
    - submit_pre_url, submit_post_url, submit_review_url --}}
    <x-test.modals :courses="$courses" />

    @push('scripts')
    {{-- Lucide Icons & search filter --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }

                const searchInput = document.getElementById('course-search');
                const cards       = document.querySelectorAll('#course-grid .course-card');

                if (searchInput && cards.length) {
                    searchInput.addEventListener('input', () => {
                        const q = searchInput.value.trim().toLowerCase();
                        cards.forEach(card => {
                            const title    = card.dataset.title || '';
                            const category = card.dataset.category || '';
                            const match    = !q || title.includes(q) || category.includes(q);
                            card.style.display = match ? 'block' : 'none';
                        });
                    });
                }
            });
    </script>
    @endpush
</x-app-layout>