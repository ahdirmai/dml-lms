{{-- resources/views/profile/edit.blade.php --}}
<x-app-layout :title="'My Profile'">
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Pengaturan Akun & Pencapaian') }}
        </h2>
    </x-slot>

    @push('styles')
    <style>
        .shadow-custom-soft {
            box-shadow:
                0 10px 15px -3px rgba(52, 152, 219, 0.10),
                0 4px 6px -2px rgba(52, 152, 219, 0.05);
        }
    </style>
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-5">
            <a href="{{ route('user.dashboard') }}" class="hover:underline">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="font-semibold text-gray-700">My Profile</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kolom kiri: tiap section = card (dibungkus di partial) --}}
            <div class="lg:col-span-2 space-y-6">
                @include('profile.partials.update-profile-information-form')
                @include('profile.partials.update-password-form')
            </div>

            {{-- Kolom kanan: ringkasan + sertifikat + hapus akun (semua card) --}}
            <aside class="space-y-6">
                {{-- Ringkasan akun (card) --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-custom-soft p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-extrabold text-emerald-600">STATUS</p>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-600">
                            Akun Aktif
                        </span>
                    </div>

                    <div class="text-xs text-gray-600 space-y-1 mb-4">
                        <p><span class="font-semibold">Bergabung:</span> {{ auth()->user()->created_at?->format('d M Y')
                            }}</p>
                        <p><span class="font-semibold">Email terverifikasi:</span> {{ auth()->user()->hasVerifiedEmail()
                            ? 'Ya' : 'Belum' }}</p>
                        <p><span class="font-semibold">Terakhir login:</span> {{
                            optional(auth()->user()->last_login_at)->format('d M Y H:i') ?? '—' }}</p>
                    </div>

                    <hr class="my-3">

                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Ringkasan Aktivitas</p>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span>Profil diperbarui (jika ada)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-800"></span>
                                <span>Ganti password (jika ada)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- SERTIFIKAT (card baru) --}}
                @include('profile.partials.certificates')

                {{-- Hapus Akun (card di dalam partial) --}}
                @include('profile.partials.delete-user-form')
            </aside>
        </div>
    </div>
</x-app-layout>
