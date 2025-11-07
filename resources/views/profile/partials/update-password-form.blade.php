<div class="bg-white rounded-2xl border border-gray-100 shadow-custom-soft p-6">
    <h3 class="text-xl font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">
        Keamanan & Password
    </h3>

    {{-- STATUS ALERT --}}
    @if (session('status') === 'password-updated')
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
        Password berhasil diperbarui.
        <button type="button" class="float-right text-emerald-700/70" @click="show=false">✕</button>
    </div>
    @endif

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        {{-- Password Saat Ini --}}
        <div>
            <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1">Password Saat
                Ini</label>
            <input id="current_password" name="current_password" type="password"
                class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                autocomplete="current-password" required>
            @error('current_password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        {{-- Password Baru --}}
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
            <input id="password" name="password" type="password"
                class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                autocomplete="new-password" required>
            @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter. Gunakan kombinasi huruf, angka, dan simbol.</p>
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi
                Password Baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                autocomplete="new-password" required>
            @error('password_confirmation') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        {{-- Tombol --}}
        <div class="flex items-center gap-2 pt-2">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-xl font-semibold text-white bg-blue-500 hover:bg-blue-600 transition shadow">
                Perbarui Password
            </button>
            <a href="{{ route('profile.edit') }}"
                class="inline-flex items-center px-4 py-2 rounded-xl font-semibold bg-gray-100 text-gray-800 hover:bg-gray-200 transition">
                Batal
            </a>
        </div>
    </form>
</div>
