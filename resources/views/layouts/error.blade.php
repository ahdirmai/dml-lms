{{-- resources/views/layouts/error.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Terjadi Kesalahan' }}</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <style>
        .bg-background-soft {
            background-color: #F5F7FA;
        }

        .text-primary-accent {
            color: #3498DB;
        }

        .bg-primary-accent {
            background-color: #3498DB;
        }

        .hover\:bg-\[\#2e82c8\]:hover {
            background-color: #2e82c8;
        }

        .text-secondary-highlight {
            color: #2ECC71;
        }
    </style>

    @stack('head')
</head>

<body class="bg-background-soft flex flex-col items-center justify-center min-h-screen text-center px-6">
    <main class="max-w-md w-full">
        <div class="bg-white shadow-xl rounded-2xl p-8">
            @yield('content')
        </div>

        <footer class="mt-8 text-gray-400 text-sm">
            &copy; {{ date('Y') }} <span class="font-semibold text-dark">ahdirmai-tech</span>. Semua hak dilindungi.
        </footer>
    </main>

    @stack('scripts')
</body>

</html>
