{{-- resources/views/profile/settings.blade.php --}}
<x-app-layout title="Pengaturan Akun">
    <x-slot:header>
        Pengaturan Akun
    </x-slot:header>

    <div class="max-w-6xl mx-auto py-4 sm:py-6 lg:py-8 space-y-6 sm:space-y-8">
        {{-- User summary --}}
        <section
            class="bg-white border border-gray-100 rounded-2xl shadow-sm px-4 sm:px-6 py-4 sm:py-5 flex items-start justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div
                    class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-base sm:text-lg">
                    {{ strtoupper(Str::substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-0.5">Masuk sebagai</p>
                    <p class="font-semibold text-gray-900 leading-tight">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs sm:text-sm text-gray-500">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </div>

            @php
            /** @var \App\Models\User|null $me */
            $me = auth()->user();
            $roleNames = $me?->getRoleNames() ?? collect();
            $activeRole = $me?->active_role ?? $roleNames->first();
            @endphp
            @if ($activeRole)
            <span
                class="inline-flex items-center rounded-full bg-slate-50 border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700 whitespace-nowrap">
                Role: {{ ucfirst($activeRole) }}
            </span>
            @endif
        </section>

        {{-- Tabs --}}
        <nav class="bg-white border border-gray-100 rounded-2xl shadow-sm px-3 sm:px-4 py-2.5 sm:py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="flex-1 overflow-x-auto">
                    <div class="flex gap-2 sm:gap-3 text-xs sm:text-sm whitespace-nowrap" id="settings-tabs">
                        <button type="button" data-target="#profile"
                            class="settings-tab inline-flex items-center gap-1.5 rounded-full px-3 sm:px-4 py-1.5 border border-transparent bg-slate-900 text-white text-xs sm:text-sm font-medium">
                            <span>Profil</span>
                        </button>
                        <button type="button" data-target="#password"
                            class="settings-tab inline-flex items-center gap-1.5 rounded-full px-3 sm:px-4 py-1.5 border border-slate-200 bg-slate-50 text-slate-700 text-xs sm:text-sm">
                            <span>Keamanan</span>
                        </button>
                        <button type="button" data-target="#certificates"
                            class="settings-tab inline-flex items-center gap-1.5 rounded-full px-3 sm:px-4 py-1.5 border border-slate-200 bg-slate-50 text-slate-700 text-xs sm:text-sm">
                            <span>Sertifikat</span>
                        </button>
                    </div>
                </div>
                <div class="hidden sm:flex text-[11px] text-gray-400 items-center gap-1">
                    <span class="w-1 h-1 rounded-full bg-emerald-400"></span>
                    <span>Perubahan disimpan saat kamu klik Simpan</span>
                </div>
            </div>
        </nav>

        <div class="grid gap-6 lg:gap-8 lg:grid-cols-3">
            <div class="space-y-6 lg:space-y-6 lg:col-span-2">
                {{-- Profil --}}
                <section id="profile" class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 sm:p-6">
                    <header class="mb-4 sm:mb-5">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                            Informasi Profil
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">
                            Perbarui nama, email, dan informasi dasar akunmu.
                        </p>
                    </header>

                    <div class="border-t border-gray-100 pt-4 sm:pt-5">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </section>

                {{-- Password --}}
                <section id="password" class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 sm:p-6">
                    <header class="mb-4 sm:mb-5">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                            Keamanan & Kata Sandi
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">
                            Ubah kata sandi secara berkala untuk menjaga akunmu tetap aman.
                        </p>
                    </header>

                    <div class="border-t border-gray-100 pt-4 sm:pt-5">
                        @include('profile.partials.update-password-form')
                    </div>
                </section>
            </div>

            {{-- Sertifikat --}}
            <section id="certificates"
                class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 sm:p-6 lg:col-span-1">
                <header class="mb-4 sm:mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                            Sertifikat
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">
                            Lihat dan kelola sertifikat yang terhubung dengan akunmu.
                        </p>
                    </div>
                </header>

                <div class="border-t border-gray-100 pt-4 sm:pt-5">
                    @include('profile.partials.certificates')
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
                const tabs = document.querySelectorAll('.settings-tab');

                const activateTab = (target) => {
                    tabs.forEach(tab => {
                        const isActive = tab.dataset.target === target;
                        tab.classList.toggle('bg-slate-900', isActive);
                        tab.classList.toggle('text-white', isActive);
                        tab.classList.toggle('border-transparent', isActive);
                        tab.classList.toggle('bg-slate-50', !isActive);
                        tab.classList.toggle('text-slate-700', !isActive);
                        tab.classList.toggle('border-slate-200', !isActive);
                    });
                };

                tabs.forEach(tab => {
                    tab.addEventListener('click', function () {
                        const target = this.dataset.target;
                        const el = document.querySelector(target);
                        if (!el) return;

                        activateTab(target);

                        const yOffset = -90;
                        const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
                        window.scrollTo({ top: y, behavior: 'smooth' });
                    });
                });

                if (window.location.hash) {
                    const hash = window.location.hash;
                    const exists = document.querySelector(hash);
                    if (exists) {
                        const tab = [...tabs].find(t => t.dataset.target === hash);
                        if (tab) activateTab(hash);
                    }
                }
            });
    </script>
    @endpush
</x-app-layout>