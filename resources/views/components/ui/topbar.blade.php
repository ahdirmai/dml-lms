{{-- resources/views/components/ui/topbar.blade.php --}}
@props(['avatar' => null, 'header' => null])

{{-- Margin bawah (mb-6) hanya akan ada di layar besar (lg) --}}
<header class="flex justify-between items-center bg-white p-4 rounded-xl shadow lg:mb-6 relative">

    {{-- Tombol Hamburger (hanya terlihat di layar kecil) --}}
    <button id="open-sidebar" class="lg:hidden p-2 -ml-2 text-dark rounded-md flex-shrink-0">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
            </path>
        </svg>
    </button>

    {{-- Header (Hanya terlihat di layar besar) --}}
    <div class="relative w-full max-w-sm lg:max-w-none flex-grow mx-2 overflow-hidden truncate hidden lg:block">
        @isset($header)
        <h2 class="text-xl font-semibold text-dark truncate">
            {{ $header }}
        </h2>
        @endisset
    </div>

    {{-- Notification + Avatar --}}
    {{-- Margin kiri disesuaikan untuk mobile (ml-2) dan desktop (lg:ml-auto) --}}
    <div class="flex items-center space-x-2 sm:space-x-4 ml-2 lg:ml-auto flex-shrink-0">
        <button class="relative p-2 text-dark hover:text-dark bg-soft rounded-full">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.4-1.4A2 2 0 0118 13.5V10a6 6 0 00-6-6H5v10l2 2v2h10z" />
            </svg>
            <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-accent"></span>
        </button>

        @php
        $user = auth()->user();
        $roleNames = $user?->getRoleNames() ?? collect();
        $activeRole = $user?->active_role ?? $roleNames->first();
        @endphp

        {{-- Avatar + Dropdown --}}
        <div class="relative">
            <button id="avatarBtn"
                class="flex items-center focus:outline-none focus:ring-2 focus:ring-brand rounded-full">
                @if($avatar)
                <img src="{{ $avatar }}" alt="User Avatar"
                    class="w-10 h-10 rounded-full border-2 border-brand shadow object-cover">
                @else
                <div
                    class="w-10 h-10 rounded-full bg-brand text-white flex items-center justify-center text-sm font-bold border-2 border-brand shadow uppercase">
                    {{ Str::substr($user->name ?? 'User', 0, 2) }}
                </div>
                @endif
            </button>

            <div id="dropdownProfile"
                class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-soft overflow-hidden z-50">
                <div class="px-4 py-3 border-b border-soft">
                    <p class="text-sm font-semibold text-dark">{{ $user?->name }}</p>
                    <p class="text-xs text-dark/60 truncate">{{ $user?->email }}</p>
                </div>

                {{-- Active Role --}}
                @if($roleNames->count() > 0)
                <div class="px-4 py-3 border-b border-soft">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs uppercase tracking-wide text-dark/60">Active Role</span>
                        <span
                            class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-brand/10 text-brand border border-brand/20">
                            {{ Str::headline($activeRole) }}
                        </span>
                    </div>

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

                {{-- Profile & Logout --}}
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

{{-- Vanilla JS for Dropdown --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const avatarBtn = document.getElementById('avatarBtn');
        const dropdown = document.getElementById('dropdownProfile');

        if (avatarBtn && dropdown) {
            avatarBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!dropdown.classList.contains('hidden') && !avatarBtn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }
    });
</script>
