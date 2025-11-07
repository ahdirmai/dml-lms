@extends('layouts.error', ['title' => 'Akses Ditolak'])

@section('content')
<div class="flex flex-col items-center">
    <div class="text-8xl font-extrabold text-yellow-500 mb-4">403</div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Akses Ditolak</h1>
    <p class="text-gray-500 mb-8">
        Anda tidak memiliki izin untuk mengakses halaman ini.
    </p>
    <a href="{{ url('/') }}"
        class="bg-primary-accent hover:bg-[#2e82c8] text-white px-6 py-3 rounded-xl font-semibold shadow transition">
        Kembali ke Beranda
    </a>
</div>
@endsection
