{{-- resources/views/errors/500.blade.php --}}
@extends('layouts.error', ['title' => 'Terjadi Kesalahan Server'])

@section('content')
<div class="flex flex-col items-center">
    <div class="text-8xl font-extrabold text-red-500 mb-4">500</div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Terjadi Kesalahan di Server</h1>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Maaf, terjadi kesalahan tak terduga di sistem kami.<br>
        Tim teknis sudah diberitahu dan sedang memperbaikinya.
    </p>

    <div class="flex space-x-3">
        <button onclick="window.location.reload()"
            class="bg-primary-accent hover:bg-[#2e82c8] text-white px-6 py-3 rounded-xl font-semibold shadow transition">
            Muat Ulang Halaman
        </button>

        <a href="{{ url('/') }}"
            class="border border-gray-300 text-gray-700 hover:bg-gray-100 px-6 py-3 rounded-xl font-semibold shadow transition">
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
