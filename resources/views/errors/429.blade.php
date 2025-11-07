{{-- resources/views/errors/429.blade.php --}}
@extends('layouts.error', ['title' => 'Terlalu Banyak Permintaan'])

@section('content')
<div class="flex flex-col items-center">
    <div class="text-8xl font-extrabold text-purple-500 mb-4">429</div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Terlalu Banyak Permintaan</h1>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Anda telah melakukan terlalu banyak permintaan dalam waktu singkat.<br>
        Silakan tunggu beberapa saat sebelum mencoba kembali.
    </p>

    <div class="flex space-x-3">
        <button onclick="window.location.reload()"
            class="bg-primary-accent hover:bg-[#2e82c8] text-white px-6 py-3 rounded-xl font-semibold shadow transition">
            Coba Lagi
        </button>

        <a href="{{ url('/') }}"
            class="border border-gray-300 text-gray-700 hover:bg-gray-100 px-6 py-3 rounded-xl font-semibold shadow transition">
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
