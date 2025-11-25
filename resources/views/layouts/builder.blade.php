<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DML Learning Management System')</title>

    {{-- Google Font: Inter (utama) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN (khusus halaman builder) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        /* Palet utama, sinkron dengan tailwind.config.js */
                        soft: "#F4F6F8",
                        dark: "#343A40",
                        brand: "#09759A",
                        accent: "#37BCD8",
                        danger: "#B00E24",

                        // Compat nama lama
                        "primary-accent": "#3498DB",
                        "secondary-highlight": "#2ECC71",
                        "background-soft": "#F5F7FA",

                        // Tambahan leaderboard (kalau dipakai)
                        "green-prd": "#DFF5E1",
                        "red-prd": "#FBE5E5",
                        "medal-gold": "#FFC000",
                        "medal-silver": "#C0C0C0",
                        "medal-bronze": "#CD7F32",
                    },
                    fontFamily: {
                        // Font utama app
                        sans: [
                            "Inter",
                            "system-ui",
                            "-apple-system",
                            "BlinkMacSystemFont",
                            "Segoe UI",
                            "Roboto",
                            "Helvetica Neue",
                            "Arial",
                            "Noto Sans",
                            "sans-serif",
                            "Apple Color Emoji",
                            "Segoe UI Emoji",
                            "Segoe UI Symbol",
                            "Noto Color Emoji",
                        ],
                    },
                    boxShadow: {
                        "custom-soft":
                            "0 10px 15px -3px rgba(9,117,154,0.07), 0 4px 6px -2px rgba(9,117,154,0.04)",
                    },
                    borderRadius: {
                        xl: "1rem",   // 16px
                        "2xl": "1.5rem", // 24px
                    },
                },
            },
        };
    </script>

    @stack('styles')
</head>

<body class="bg-soft min-h-screen font-sans">
    @yield('content')

    @stack('scripts')
</body>

</html>