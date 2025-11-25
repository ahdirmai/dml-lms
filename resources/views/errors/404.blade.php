{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.error', ['title' => 'Halaman Tidak Ditemukan'])

@section('content')
<div class="flex flex-col items-center">
    <div class="text-8xl font-extrabold text-primary-accent mb-4">404</div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Halaman Tidak Ditemukan</h1>
    <p class="text-gray-500 mb-8">
        Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.
    </p>

    <a href="{{ url('/') }}"
        class="bg-primary-accent hover:bg-[#2e82c8] text-white px-6 py-3 rounded-xl font-semibold shadow transition">
        Kembali ke Beranda
    </a>
</div>
@endsection
