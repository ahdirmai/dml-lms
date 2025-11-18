<div class="bg-white shadow-sm rounded-xl p-6 border border-gray-100">
    <div class="mb-4 flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Sertifikat</h2>
            <p class="text-sm text-gray-500">
                Kelola sertifikat yang terhubung dengan akun Anda.
            </p>
        </div>
        {{-- Tombol tambah atau upload --}}
        {{-- <form action="{{ route('certificates.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label
                class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium border border-gray-200 hover:bg-gray-50 cursor-pointer">
                <span>Upload Sertifikat</span>
                <input type="file" name="certificate" class="hidden" onchange="this.form.submit()">
            </label>
        </form> --}}
    </div>

    <div class="border-t border-gray-100 pt-4">
        <ul class="divide-y divide-gray-100 text-sm">
            @forelse ($certificates ?? [] as $certificate)
            <li class="py-3 flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800">
                        {{ $certificate->name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        Diunggah: {{ $certificate->created_at->format('d M Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('certificates.download', $certificate) }}"
                        class="text-xs underline hover:no-underline">
                        Unduh
                    </a>

                    {{-- Jika tombol hapus ingin diaktifkan --}}
                    {{--
                    <form action="{{ route('certificates.destroy', $certificate) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">
                            Hapus
                        </button>
                    </form>
                    --}}
                </div>
            </li>
            @empty
            <li class="py-3">
                <p class="text-sm text-gray-500">Belum ada sertifikat.</p>
            </li>
            @endforelse
        </ul>
    </div>
</div>