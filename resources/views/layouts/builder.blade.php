<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DML Learning Management System')</title>

    {{-- Tailwind via CDN (cukup untuk builder) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary-accent": "#3498DB",
                        "secondary-highlight": "#2ECC71",
                        "background-soft": "#F5F7FA",
                    },
                    fontFamily: {
                        sans: ["Nunito", "Poppins", "sans-serif"],
                    },
                    boxShadow: {
                        "custom-soft": "0 10px 15px -3px rgba(52, 152, 219, 0.1), 0 4px 6px -2px rgba(52, 152, 219, 0.05)",
                    },
                },
            },
        };
    </script>

    @stack('styles')
</head>

<body class="bg-background-soft min-h-screen">
    @yield('content')

    @stack('scripts')
</body>

</html>
