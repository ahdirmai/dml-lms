{{-- resources/views/errors/401.blade.php --}}
@extends('layouts.error', ['title' => 'Tidak Diizinkan'])

@section('content')
<div class="flex flex-col items-center">
    <div class="text-8xl font-extrabold text-orange-500 mb-4">401</div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Tidak Diizinkan</h1>
    <p class="text-gray-500 mb-8">
        Anda tidak memiliki otorisasi untuk mengakses halaman ini.<br>
        Silakan login terlebih dahulu untuk melanjutkan.
    </p>

    <div class="flex space-x-3">
        <a href="{{ route('login') }}"
            class="bg-primary-accent hover:bg-[#2e82c8] text-white px-6 py-3 rounded-xl font-semibold shadow transition">
            Masuk
        </a>
        <a href="{{ url('/') }}"
            class="border border-gray-300 text-gray-700 hover:bg-gray-100 px-6 py-3 rounded-xl font-semibold shadow transition">
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
