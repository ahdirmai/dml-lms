{{-- resources/views/profile/partials/update-profile-information-form.blade.php --}}
@php
/** @var \App\Models\User $user */
$user = auth()->user();
@endphp

<form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
    @csrf
    @method('PATCH')

    <x-ui.form-field label="Nama Lengkap" name="name">
        <x-ui.input name="name" type="text" :value="old('name', $user->name)" autocomplete="name" />
    </x-ui.form-field>

    <x-ui.form-field label="Alamat Email" name="email" help="Pastikan email aktif, digunakan untuk notifikasi.">
        <x-ui.input name="email" type="email" :value="old('email', $user->email)" autocomplete="email" />
    </x-ui.form-field>

    {{-- Contoh tambahan field lain, kalau ada --}}
    {{--
    <x-ui.form-field label="Nomor Telepon" name="phone">
        <x-ui.input name="phone" type="tel" :value="old('phone', $user->phone)" autocomplete="tel" />
    </x-ui.form-field>
    --}}

    <div class="flex items-center justify-end gap-3 pt-1">
        @if (session('status') === 'profile-updated')
        <p class="text-xs text-emerald-600">
            Perubahan profil berhasil disimpan.
        </p>
        @endif

        <button type="submit"
            class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-medium bg-slate-900 text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900">
            Simpan Perubahan
        </button>
    </div>
</form>