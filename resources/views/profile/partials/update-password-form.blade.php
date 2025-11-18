{{-- resources/views/profile/partials/update-password-form.blade.php --}}
<form method="POST" action="{{ route('password.update') }}" class="space-y-5">
    @csrf
    @method('PUT')

    <x-ui.form-field label="Kata Sandi Saat Ini" name="current_password">
        <x-ui.input name="current_password" type="password" autocomplete="current-password" />
    </x-ui.form-field>

    <x-ui.form-field label="Kata Sandi Baru" name="password"
        help="Gunakan minimal 8 karakter dengan kombinasi huruf & angka.">
        <x-ui.input name="password" type="password" autocomplete="new-password" />
    </x-ui.form-field>

    <x-ui.form-field label="Konfirmasi Kata Sandi Baru" name="password_confirmation">
        <x-ui.input name="password_confirmation" type="password" autocomplete="new-password" />
    </x-ui.form-field>

    <div class="flex items-center justify-end gap-3 pt-1">
        @if (session('status') === 'password-updated')
        <p class="text-xs text-emerald-600">
            Kata sandi berhasil diperbarui.
        </p>
        @endif

        <button type="submit"
            class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-medium bg-slate-900 text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900">
            Perbarui Kata Sandi
        </button>
    </div>
</form>