@php $user = auth()->user(); @endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-custom-soft p-6">
    <h3 class="text-xl font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">
        Informasi Pribadi
    </h3>

    {{-- STATUS ALERT --}}
    @if (session('status') === 'profile-updated')
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
        Profil berhasil diperbarui.
        <button type="button" class="float-right text-emerald-700/70" @click="show=false">✕</button>
    </div>
    @endif

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        {{-- Nama --}}
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                autocomplete="name" required>
            @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                autocomplete="username" required>
            @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror

            {{-- Verifikasi Email (opsional) --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                Email Anda belum terverifikasi.
                <form method="post" action="{{ route('verification.send') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="ml-2 font-semibold text-amber-900 underline underline-offset-2 hover:opacity-80">
                        Kirim ulang tautan verifikasi
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- Tombol --}}
        <div class="flex items-center gap-2 pt-2">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-xl font-semibold text-white bg-blue-500 hover:bg-blue-600 transition shadow">
                Simpan Perubahan
            </button>
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-4 py-2 rounded-xl font-semibold bg-gray-100 text-gray-800 hover:bg-gray-200 transition">
                Batal
            </a>
        </div>
    </form>
</div>
