<div x-data="{ open: false }" class="bg-white rounded-2xl border border-gray-100 shadow-custom-soft p-6">
    <h3 class="text-xl font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">
        Hapus Akun
    </h3>

    {{-- STATUS / ERROR (opsional) --}}
    @if (session('status') === 'account-deleted')
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">
        Akun telah dijadwalkan untuk dihapus.
        <button type="button" class="float-right text-rose-700/70" @click="show=false">✕</button>
    </div>
    @endif

    <p class="text-sm text-gray-600 mb-4">
        Menghapus akun bersifat permanen dan tidak dapat dipulihkan. Semua data terkait pembelajaran akan dihapus.
    </p>

    <button type="button" @click="open = true; $nextTick(() => document.getElementById('confirm_password').focus())"
        class="inline-flex items-center px-4 py-2 rounded-xl font-semibold text-white bg-rose-600 hover:bg-rose-700 transition shadow">
        Hapus Akun
    </button>

    {{-- Overlay --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/40" aria-hidden="true"></div>

    {{-- Modal --}}
    <div x-show="open" x-trap.noscroll="open" x-transition
        class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true"
        aria-labelledby="modal-title">
        <div class="w-full max-w-md bg-white rounded-2xl border border-gray-100 shadow-xl p-6">
            <h4 id="modal-title" class="text-lg font-bold text-gray-800">Konfirmasi Hapus Akun</h4>
            <p class="mt-2 text-sm text-gray-600">Masukkan password untuk konfirmasi penghapusan akun secara permanen.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-4 space-y-4">
                @csrf
                @method('delete')

                <div>
                    <label for="confirm_password"
                        class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                    <input id="confirm_password" name="password" type="password"
                        class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 transition"
                        placeholder="********" required>
                    @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="open = false"
                        class="inline-flex items-center px-4 py-2 rounded-xl font-semibold bg-gray-100 text-gray-800 hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-xl font-semibold text-white bg-rose-600 hover:bg-rose-700 transition shadow">
                        Hapus Permanen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
