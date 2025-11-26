{{-- resources/views/admin/pages/users/index.blade.php --}}
<x-app-layout :title="'Users Management'">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-bold text-xl text-dark leading-tight">Users Management</h2>
            <p class="text-sm text-dark/60">Manage system users, roles, and access permissions.</p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto space-y-6">

            {{-- Alerts --}}
            @if (session('success'))
            <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif

            {{-- Toolbar / Filters --}}
            <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    {{-- Search & Filter --}}
                    <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto flex-1">
                        <div class="relative w-full sm:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-ui.icon name="search" class="h-4 w-4 text-gray-400" />
                            </div>
                            <x-ui.input type="text" name="q" value="{{ $q ?? request('q') }}"
                                placeholder="Search by name or email..." class="pl-9 w-full" />
                        </div>
                        
                        <div class="w-full sm:w-48">
                            <x-ui.select name="role" class="w-full">
                                <option value="">All Roles</option>
                                @foreach($roles as $r)
                                <option value="{{ $r }}" @selected(($role ?? request('role'))===$r)>{{ Str::headline($r) }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <div class="flex gap-2">
                            <x-ui.button type="submit" variant="secondary" class="whitespace-nowrap">
                                <x-ui.icon name="filter" class="w-4 h-4 mr-2" /> Filter
                            </x-ui.button>
                            
                            @if(request()->hasAny(['q','role']) && (filled($q ?? request('q')) || filled($role ?? request('role'))))
                            <x-ui.button as="a" href="{{ route('admin.users.index') }}" variant="ghost" class="text-gray-500 hover:text-dark">
                                Reset
                            </x-ui.button>
                            @endif
                        </div>
                    </form>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                        <x-ui.button as="a" href="{{ route('admin.integration.users.index') }}" variant="outline" class="whitespace-nowrap">
                            <x-ui.icon name="refresh" class="w-4 h-4 mr-2" /> Sync Users
                        </x-ui.button>
                        <x-ui.button type="button" id="btnOpenCreate" variant="primary" class="whitespace-nowrap shadow-md shadow-brand/20">
                            <x-ui.icon name="plus" class="w-4 h-4 mr-2" /> New User
                        </x-ui.button>
                    </div>
                </div>
            </div>

            {{-- ===== TABLE (Desktop/Tablet) ===== --}}
            <div class="bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden hidden md:block">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                                <th class="px-6 py-4">User Info</th>
                                <th class="px-6 py-4">Roles</th>
                                <th class="px-6 py-4">Active Role</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $u)
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-brand/10 text-brand flex items-center justify-center text-sm font-bold shrink-0">
                                            {{ substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-dark">{{ $u->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($u->roles as $r)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            {{ Str::headline($r->name) }}
                                        </span>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    @if($u->active_role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                                        {{ Str::headline($u->active_role) }}
                                    </span>
                                    @else
                                    <span class="text-sm text-gray-400 italic">No active role</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" 
                                            class="p-2 text-gray-400 hover:text-brand hover:bg-brand/5 rounded-lg transition-colors btn-edit"
                                            title="Edit User"
                                            data-action="{{ route('admin.users.update', $u) }}" 
                                            data-name="{{ $u->name }}"
                                            data-email="{{ $u->email }}" 
                                            data-active="{{ $u->active_role ?? '' }}"
                                            data-roles='@json($u->roles->pluck("name"))'>
                                            <x-ui.icon name="pencil" class="w-4 h-4" />
                                        </button>

                                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete User">
                                                <x-ui.icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                            <x-ui.icon name="users" class="w-6 h-6 text-gray-400" />
                                        </div>
                                        <h3 class="text-lg font-medium text-dark">No users found</h3>
                                        <p class="text-sm mb-4">Try adjusting your search or filter to find what you're looking for.</p>
                                        <x-ui.button type="button" id="btnOpenCreateInline" variant="primary" size="sm">
                                            <x-ui.icon name="plus" class="w-4 h-4 mr-2" /> Create New User
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $users->links() }}
                </div>
                @endif
            </div>

            {{-- ===== CARD LIST (Mobile) ===== --}}
            <div class="space-y-4 md:hidden">
                @forelse($users as $u)
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-brand/10 text-brand flex items-center justify-center text-sm font-bold shrink-0">
                                {{ substr($u->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-semibold text-dark truncate">{{ $u->name }}</h3>
                                <p class="text-sm text-gray-500 truncate">{{ $u->email }}</p>
                            </div>
                        </div>
                        @if($u->active_role)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                            {{ Str::headline($u->active_role) }}
                        </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-1">
                        @foreach($u->roles as $r)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                            {{ Str::headline($r->name) }}
                        </span>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex gap-2">
                        <x-ui.button type="button" class="flex-1 btn-edit" variant="outline" size="sm"
                            data-action="{{ route('admin.users.update', $u) }}" data-name="{{ $u->name }}"
                            data-email="{{ $u->email }}" data-active="{{ $u->active_role ?? '' }}"
                            data-roles='@json($u->roles->pluck("name"))'>
                            Edit
                        </x-ui.button>

                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="flex-1"
                            onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <x-ui.button type="submit" class="w-full" variant="danger" size="sm">Delete</x-ui.button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 text-center">
                    <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <x-ui.icon name="users" class="w-6 h-6 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-dark">No users found</h3>
                    <div class="mt-4">
                        <x-ui.button type="button" id="btnOpenCreateMobile" variant="primary">Create User</x-ui.button>
                    </div>
                </div>
                @endforelse

                <div class="mt-4">{{ $users->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ================== MODALS ================== --}}

    {{-- Create Modal --}}
    <div id="modalCreate" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" data-close="true"></div>
        <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2
                    w-full sm:w-[40rem] bg-white sm:rounded-2xl shadow-2xl overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-lg text-dark">Create New User</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" data-close="true">
                    <x-ui.icon name="x" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-6 max-h-[85vh] overflow-y-auto">
                @include('admin.pages.users._form', [
                'action' => route('admin.users.store'),
                'method' => 'POST',
                'user' => null,
                'roles' => $roles,
                'userRoles' => [],
                ])
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="modalEdit" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" data-close="true"></div>
        <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2
                    w-full sm:w-[40rem] bg-white sm:rounded-2xl shadow-2xl overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-lg text-dark">Edit User</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" data-close="true">
                    <x-ui.icon name="x" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-6 max-h-[85vh] overflow-y-auto">
                @include('admin.pages.users._form', [
                'action' => '#',
                'method' => 'POST',
                'user' => null,
                'roles' => $roles,
                'userRoles' => [],
                ])
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
        // ===== Helpers
        const lock = (v) => document.body.style.overflow = v ? 'hidden' : '';
        const open = (el) => { 
            el.classList.remove('hidden'); 
            // Small animation delay
            requestAnimationFrame(() => {
                el.querySelector('.absolute.inset-0').classList.add('opacity-100');
                el.querySelector('.absolute.inset-x-0').classList.add('translate-y-0', 'opacity-100');
            });
            lock(true); 
        };
        const close = (el) => { 
            el.classList.add('hidden'); 
            lock(false); 
        };

        // ===== Modal Logic
        ['modalCreate', 'modalEdit'].forEach(id => {
            const modal = document.getElementById(id);
            if (!modal) return;
            
            modal.addEventListener('click', (e) => {
                if (e.target.dataset.close) close(modal);
            });
            
            // Close on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(modal);
            });
        });

        // ===== Create Modal Triggers
        const modalCreate = document.getElementById('modalCreate');
        const createForm = modalCreate?.querySelector('form');
        
        // Inputs
        const cName = modalCreate?.querySelector('#name');
        const cEmail = modalCreate?.querySelector('#email');
        const cPwd = modalCreate?.querySelector('#password');
        const cPwd2 = modalCreate?.querySelector('#password_confirmation');
        const cRoles = modalCreate?.querySelector('#roles');
        const cActive = modalCreate?.querySelector('#active_role');

        function clearCreateForm() {
            if (!createForm) return;
            createForm.action = "{{ route('admin.users.store') }}";
            
            const m = createForm.querySelector('input[name="_method"]');
            if (m) m.remove();
            
            createForm.reset();
            
            // Clear specific fields
            [cName, cEmail, cPwd, cPwd2].forEach(el => { if(el) el.value = ''; });
            
            // Reset Roles & Trigger Sync
            if (cRoles) {
                Array.from(cRoles.options).forEach(o => o.selected = false);
                cRoles.dispatchEvent(new Event('change'));
            }
        }

        ['btnOpenCreate', 'btnOpenCreateInline', 'btnOpenCreateMobile'].forEach(id => {
            const btn = document.getElementById(id);
            if (btn) {
                btn.addEventListener('click', () => {
                    clearCreateForm();
                    open(modalCreate);
                });
            }
        });

        // ===== Edit Modal Triggers
        const modalEdit = document.getElementById('modalEdit');
        const editForm = modalEdit?.querySelector('form');
        const eName = modalEdit?.querySelector('#name');
        const eEmail = modalEdit?.querySelector('#email');
        const ePwd = modalEdit?.querySelector('#password');
        const ePwd2 = modalEdit?.querySelector('#password_confirmation');
        const eRoles = modalEdit?.querySelector('#roles');
        const eActive = modalEdit?.querySelector('#active_role');

        function ensureMethodPut(form) {
            let m = form.querySelector('input[name="_method"]');
            if (!m) {
                m = document.createElement('input');
                m.type = 'hidden'; m.name = '_method'; m.value = 'PUT';
                form.appendChild(m);
            } else {
                m.value = 'PUT';
            }
        }

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!modalEdit || !editForm) return;

                editForm.action = btn.dataset.action || '#';
                ensureMethodPut(editForm);

                if (eName) eName.value = btn.dataset.name || '';
                if (eEmail) eEmail.value = btn.dataset.email || '';
                if (ePwd) ePwd.value = '';
                if (ePwd2) ePwd2.value = '';

                // Handle Roles
                let roles = [];
                try { roles = JSON.parse(btn.dataset.roles || '[]'); } catch(e) {}
                
                if (eRoles) {
                    Array.from(eRoles.options).forEach(opt => {
                        opt.selected = roles.includes(opt.value);
                    });
                    // Trigger change to update badges and rebuild active role options
                    eRoles.dispatchEvent(new Event('change'));
                }

                // Handle Active Role (after options are rebuilt)
                const currentActive = btn.dataset.active || '';
                if (eActive) {
                    eActive.value = currentActive;
                }

                open(modalEdit);
            });
        });

        // ===== Role Picker Logic
        function initRolePicker(container) {
            const realSelect = container?.querySelector('#roles');
            const picker = container?.querySelector('#roles_picker');
            const badgeContainer = container?.querySelector('#roles_badges');
            
            if (!realSelect || !picker || !badgeContainer) return;

            const emptyText = badgeContainer.querySelector('.empty-text');

            function updateBadges() {
                // Clear existing badges (keep empty text if needed, but easier to rebuild)
                badgeContainer.innerHTML = '';
                
                const selectedOptions = Array.from(realSelect.selectedOptions);
                
                if (selectedOptions.length === 0) {
                    if (emptyText) badgeContainer.appendChild(emptyText);
                    else badgeContainer.innerHTML = '<span class="text-sm text-gray-400 italic self-center empty-text">No roles selected</span>';
                    return;
                }

                selectedOptions.forEach(opt => {
                    const badge = document.createElement('span');
                    badge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-brand/10 text-brand border border-brand/20';
                    badge.innerHTML = `
                        ${opt.text}
                        <button type="button" class="ml-1.5 text-brand/60 hover:text-brand focus:outline-none" data-value="${opt.value}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    `;
                    badgeContainer.appendChild(badge);
                });
            }

            // Picker Change
            picker.addEventListener('change', () => {
                const val = picker.value;
                if (!val) return;

                // Select in real select
                const opt = Array.from(realSelect.options).find(o => o.value === val);
                if (opt) {
                    opt.selected = true;
                    realSelect.dispatchEvent(new Event('change')); // Trigger sync
                }
                
                picker.value = ''; // Reset picker
            });

            // Remove Badge
            badgeContainer.addEventListener('click', (e) => {
                const btn = e.target.closest('button');
                if (!btn) return;
                
                const val = btn.dataset.value;
                const opt = Array.from(realSelect.options).find(o => o.value === val);
                if (opt) {
                    opt.selected = false;
                    realSelect.dispatchEvent(new Event('change')); // Trigger sync
                }
            });

            // Listen to Real Select Change (to update badges and sync active role)
            realSelect.addEventListener('change', updateBadges);
            
            // Initial update
            updateBadges();
        }

        initRolePicker(modalCreate);
        initRolePicker(modalEdit);

        // ===== Sync Active Role Options (Modified to work with hidden select)
        function syncActiveRole(container) {
            const rolesSelect = container?.querySelector('#roles');
            const activeSelect = container?.querySelector('#active_role');
            
            if (!rolesSelect || !activeSelect) return;

            rolesSelect.addEventListener('change', () => {
                const selectedRoles = Array.from(rolesSelect.selectedOptions).map(o => o.value);
                const currentActive = activeSelect.value;
                
                // Keep "None" option
                activeSelect.innerHTML = '<option value="">— None —</option>';
                
                selectedRoles.forEach(r => {
                    // Find text from hidden select option
                    const optText = Array.from(rolesSelect.options).find(o => o.value === r)?.text || r;
                    
                    const o = document.createElement('option');
                    o.value = r;
                    o.textContent = optText;
                    activeSelect.appendChild(o);
                });
                
                if (selectedRoles.includes(currentActive)) {
                    activeSelect.value = currentActive;
                } else {
                    activeSelect.value = '';
                }
            });
        }

        syncActiveRole(modalCreate);
        syncActiveRole(modalEdit);

    })();
    </script>
    @endpush
</x-app-layout>