@props(['avatar' => null])

<header class="flex justify-between items-center bg-white p-4 rounded-xl shadow mb-6">
    {{-- 🔍 Search Bar --}}
    <div class="relative w-full max-w-sm">
        <input type="text" placeholder="Cari kursus, materi, atau kreator…"
            class="pl-10 pr-4 py-2 border border-soft rounded-xl w-full focus:outline-none focus:ring-2 focus:ring-brand transition">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark/60" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6M10 4a6 6 0 100 12 6 6 0 000-12" />
        </svg>
    </div>

    {{-- 🔔 Notification + Avatar --}}
    <div class="flex items-center space-x-4 ml-4">
        <button class="relative p-2 text-dark hover:text-dark bg-soft rounded-full">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.4-1.4A2 2 0 0118 13.5V10a6 6 0 00-6-6H5v10l2 2v2h10z" />
            </svg>
            <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-accent"></span>
        </button>

        {{-- 👤 Avatar + Dropdown --}}
        @php
        $user = auth()->user();
        $roleNames = $user?->getRoleNames() ?? collect();
        $activeRole = $user?->active_role ?? $roleNames->first();
        @endphp

        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open"
                class="flex items-center focus:outline-none focus:ring-2 focus:ring-brand rounded-full">
                <img src="{{ $avatar ?? 'https://via.placeholder.com/40' }}" alt="User Avatar"
                    class="w-10 h-10 rounded-full border-2 border-brand shadow">
            </button>

            {{-- Dropdown --}}
            <div x-cloak x-show="open" x-transition
                class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-soft overflow-hidden z-50">
                <div class="px-4 py-3 border-b border-soft">
                    <p class="text-sm font-semibold text-dark">{{ $user?->name }}</p>
                    <p class="text-xs text-dark/60 truncate">{{ $user?->email }}</p>
                </div>

                {{-- 🌐 Active Role --}}
                @if($roleNames->count() > 0)
                <div class="px-4 py-3 border-b border-soft">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs uppercase tracking-wide text-dark/60">Active Role</span>
                        <span
                            class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-brand/10 text-brand border border-brand/20">
                            {{ Str::headline($activeRole) }}
                        </span>
                    </div>

                    {{-- Switcher --}}
                    @if($roleNames->count() > 1)
                    <form action="{{ route('switch.role') }}" method="POST">
                        @csrf
                        <select name="role" onchange="this.form.submit()"
                            class="w-full rounded-lg border border-soft px-2 py-1.5 text-sm text-dark bg-white focus:ring-2 focus:ring-brand focus:border-brand">
                            @foreach($roleNames as $role)
                            <option value="{{ $role }}" @selected($activeRole===$role)>
                                {{ Str::headline($role) }}
                            </option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </div>
                @endif

                {{-- ⚙️ Profile & Logout --}}
                <div class="py-2">
                    <a href="{{ route('profile.edit') }}"
                        class="block px-4 py-2.5 text-sm text-dark hover:bg-soft transition">
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2.5 text-sm text-danger hover:bg-danger/10">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>