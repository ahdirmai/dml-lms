{{-- resources/views/admin/pages/users/index.blade.php --}}
<x-app-layout :title="'Users Management'">
    <x-slot name="header">
        Users Management
    </x-slot>

    <div class="py-6">
        <div class="mx-auto">

            {{-- Alerts --}}
            @if (session('success'))
            <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif

            {{-- Toolbar / Filters --}}
            <div class="bg-white shadow sm:rounded-lg mb-3">
                <div class="p-4 border-b border-soft flex flex-col md:flex-row md:items-center gap-3 justify-between">
                    <form method="GET"
                        class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                        <x-ui.input type="text" name="q" value="{{ $q ?? request('q') }}"
                            placeholder="Search name/email" class="w-full sm:w-64" />
                        <x-ui.select name="role">
                            <option value="">All Roles</option>
                            @foreach($roles as $r)
                            <option value="{{ $r }}" @selected(($role ?? request('role'))===$r)>{{ Str::headline($r) }}
                            </option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.button type="submit" variant="primary">Filter</x-ui.button>
                        @if(request()->hasAny(['q','role']) && (filled($q ?? request('q')) || filled($role ??
                        request('role'))))
                        <x-ui.button as="a" href="{{ route('admin.users.index') }}" variant="subtle">Reset</x-ui.button>
                        @endif
                    </form>

                    <div class="flex gap-2 w-full md:w-auto">
                        <x-ui.button type="button" id="btnOpenCreate" variant="primary" class="w-full md:w-auto">
                            + Create
                        </x-ui.button>
                        <x-ui.button as="a" href="{{ route('admin.integration.users.index') }}" variant="primary"
                            size="md">
                            Integrasi User
                        </x-ui.button>
                    </div>
                </div>
            </div>

            {{-- ===== TABLE (Desktop/Tablet) ===== --}}
            <div class="bg-white shadow sm:rounded-lg hidden md:block">
                <div class="p-4 overflow-x-auto">
                    <x-ui.table>
                        <x-ui.thead>
                            <x-ui.th>ID</x-ui.th>
                            <x-ui.th>Name</x-ui.th>
                            <x-ui.th>Email</x-ui.th>
                            <x-ui.th>Roles</x-ui.th>
                            <x-ui.th>Active</x-ui.th>
                            <x-ui.th align="right"></x-ui.th>
                        </x-ui.thead>

                        <x-ui.tbody>
                            @forelse($users as $u)
                            <x-ui.tr>
                                <x-ui.td class="text-xs text-dark/60">{{ $u->id }}</x-ui.td>

                                <x-ui.td>
                                    <div class="text-dark font-semibold">{{ $u->name }}</div>
                                </x-ui.td>

                                <x-ui.td>
                                    <div class="text-sm text-dark/60">{{ $u->email }}</div>
                                </x-ui.td>

                                <x-ui.td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($u->roles as $r)
                                        <x-ui.badge color="gray">{{ Str::headline($r->name) }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                </x-ui.td>

                                <x-ui.td>
                                    @if($u->active_role)
                                    <x-ui.badge color="brand">{{ Str::headline($u->active_role) }}</x-ui.badge>
                                    @else
                                    <span class="text-xs text-dark/50">—</span>
                                    @endif
                                </x-ui.td>

                                <x-ui.td align="right" class="whitespace-nowrap">
                                    <x-ui.button type="button" size="sm" variant="outline" class="mr-1 btn-edit"
                                        data-action="{{ route('admin.users.update', $u) }}" data-name="{{ $u->name }}"
                                        data-email="{{ $u->email }}" data-active="{{ $u->active_role ?? '' }}"
                                        data-roles='@json($u->roles->pluck("name"))'>
                                        Edit
                                    </x-ui.button>

                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <x-ui.button type="submit" size="sm" variant="danger">Delete</x-ui.button>
                                    </form>
                                </x-ui.td>
                            </x-ui.tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <x-ui.empty-state title="No users found"
                                        subtitle="Coba ubah filter atau tambah user baru.">
                                        <x-ui.button type="button" id="btnOpenCreateInline" variant="primary">Create
                                            User</x-ui.button>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                            @endforelse
                        </x-ui.tbody>
                    </x-ui.table>

                    <div class="mt-4">{{ $users->links() }}</div>
                </div>
            </div>

            {{-- ===== CARD LIST (Mobile) ===== --}}
            <div class="space-y-3 md:hidden">
                @forelse($users as $u)
                <x-ui.card class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-dark text-base truncate">{{ $u->name }}</h3>
                            <p class="text-xs text-dark/60 truncate">{{ $u->email }}</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach($u->roles as $r)
                                <x-ui.badge color="gray">{{ Str::headline($r->name) }}</x-ui.badge>
                                @endforeach
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if($u->active_role)
                            <x-ui.badge color="brand">{{ Str::headline($u->active_role) }}</x-ui.badge>
                            @else
                            <span class="text-xs text-dark/50">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-ui.button type="button" class="flex-1 min-w-[7rem] btn-edit" variant="outline"
                            data-action="{{ route('admin.users.update', $u) }}" data-name="{{ $u->name }}"
                            data-email="{{ $u->email }}" data-active="{{ $u->active_role ?? '' }}"
                            data-roles='@json($u->roles->pluck("name"))'>
                            Edit
                        </x-ui.button>

                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="flex-1 min-w-[7rem]"
                            onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <x-ui.button type="submit" class="w-full" variant="danger">Delete</x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
                @empty
                <x-ui.card class="p-6 text-center text-sm text-gray-500">
                    No users found
                    <div class="mt-3">
                        <x-ui.button type="button" id="btnOpenCreateMobile" variant="primary">Create User</x-ui.button>
                    </div>
                </x-ui.card>
                @endforelse

                <div class="mt-4">{{ $users->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ================== MODALS ================== --}}

    {{-- Create Modal --}}
    <div id="modalCreate" class="fixed inset-0 z-40 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="true" aria-hidden="true"></div>
        <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2
                    w-full sm:w-[44rem] bg-white rounded-t-2xl sm:rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="font-semibold text-dark">Create User</h3>
                <button type="button" class="p-2 rounded hover:bg-soft" data-close="true" aria-label="Close">✕</button>
            </div>
            <div class="p-4">
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

    {{-- Edit Modal (isi di-JS) --}}
    <div id="modalEdit" class="fixed inset-0 z-40 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="true" aria-hidden="true"></div>
        <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2
                    w-full sm:w-[44rem] bg-white rounded-t-2xl sm:rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="font-semibold text-dark">Edit User</h3>
                <button type="button" class="p-2 rounded hover:bg-soft" data-close="true" aria-label="Close">✕</button>
            </div>
            <div class="p-4">
                {{-- action diganti via JS; method disuntik PUT via JS; nilai field diisi via JS --}}
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
        // ===== Helpers (modal & scroll lock)
        const lock = (v)=>document.documentElement.style.overflow = v ? 'hidden' : '';
        const open = (el)=>{ el.classList.remove('hidden'); lock(true); };
        const close = (el)=>{ el.classList.add('hidden'); lock(false); };

        // ===== Create modal
        const modalCreate = document.getElementById('modalCreate');
        const btnOpenCreate = document.getElementById('btnOpenCreate');
        const btnOpenCreateInline = document.getElementById('btnOpenCreateInline');
        const btnOpenCreateMobile = document.getElementById('btnOpenCreateMobile');

        const createForm = modalCreate ? modalCreate.querySelector('form') : null;

        // Inputs create (id di _form: #name, #email, #password, #password_confirmation, #roles, #active_role)
        const cName = modalCreate ? modalCreate.querySelector('#name') : null;
        const cEmail = modalCreate ? modalCreate.querySelector('#email') : null;
        const cPwd = modalCreate ? modalCreate.querySelector('#password') : null;
        const cPwd2 = modalCreate ? modalCreate.querySelector('#password_confirmation') : null;
        const cRoles = modalCreate ? modalCreate.querySelector('#roles') : null;
        const cActive = modalCreate ? modalCreate.querySelector('#active_role') : null;

        function clearCreateForm() {
            if (!createForm) return;
            createForm.setAttribute('action', "{{ route('admin.users.store') }}");
            const m = createForm.querySelector('input[name="_method"]');
            if (m) m.remove();
            if (typeof createForm.reset === 'function') createForm.reset();
            [cName, cEmail, cPwd, cPwd2].forEach(el => { if (el) el.value = ''; });

            // Kosongkan selected roles & aktifkan opsi default active role
            if (cRoles) Array.from(cRoles.options).forEach(o => o.selected = false);
            if (cActive) {
                // rebuild options active_role: None + semua roles (boleh semua saat create)
                const allRoles = @json($roles);
                cActive.innerHTML = '';
                const optNone = document.createElement('option');
                optNone.value = ''; optNone.textContent = '— None —';
                cActive.appendChild(optNone);
                allRoles.forEach(r=>{
                    const o = document.createElement('option');
                    o.value = r; o.textContent = r.replace(/(^\w|_\w)/g, s=>s.replace('_',' ').toUpperCase());
                    cActive.appendChild(o);
                });
                cActive.value = '';
            }

            // Nonaktifkan autofill (opsional)
            createForm.setAttribute('autocomplete','off');
            [cName, cEmail, cPwd, cPwd2].forEach(el => { if (el) el.setAttribute('autocomplete','off'); });
        }

        function wireOpenCreate(btn) {
            if (!btn) return;
            btn.addEventListener('click', () => { clearCreateForm(); open(modalCreate); });
        }
        wireOpenCreate(btnOpenCreate);
        wireOpenCreate(btnOpenCreateInline);
        wireOpenCreate(btnOpenCreateMobile);

        if (modalCreate) {
            modalCreate.addEventListener('click', (e)=> { if (e.target.dataset.close) close(modalCreate); });
            document.addEventListener('keydown', (e)=> { if (e.key === 'Escape') close(modalCreate); });
        }

        // ===== Edit modal
        const modalEdit = document.getElementById('modalEdit');
        const editButtons = document.querySelectorAll('.btn-edit');
        const editForm = modalEdit ? modalEdit.querySelector('form') : null;

        // Inputs edit (ambil dari _form di dalam modalEdit)
        const eName = modalEdit ? modalEdit.querySelector('#name') : null;
        const eEmail = modalEdit ? modalEdit.querySelector('#email') : null;
        const ePwd = modalEdit ? modalEdit.querySelector('#password') : null;
        const ePwd2 = modalEdit ? modalEdit.querySelector('#password_confirmation') : null;
        const eRoles = modalEdit ? modalEdit.querySelector('#roles') : null;
        const eActive = modalEdit ? modalEdit.querySelector('#active_role') : null;

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

        function setMultiSelect(selectEl, values) {
            if (!selectEl) return;
            const set = new Set(values.map(String));
            Array.from(selectEl.options).forEach(opt => {
                opt.selected = set.has(String(opt.value));
            });
        }

        function rebuildActiveRoleOptions(selectEl, allowedRoles, currentActive) {
            if (!selectEl) return;
            selectEl.innerHTML = '';
            const optNone = document.createElement('option');
            optNone.value = ''; optNone.textContent = '— None —';
            selectEl.appendChild(optNone);
            allowedRoles.forEach(r=>{
                const o = document.createElement('option');
                o.value = r;
                // Headline-ish
                o.textContent = r.replace(/(^\w|_\w)/g, s => s.replace('_',' ').toUpperCase());
                selectEl.appendChild(o);
            });
            selectEl.value = currentActive || '';
        }

        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (!modalEdit || !editForm) return;

                // Set action & method
                editForm.setAttribute('action', btn.getAttribute('data-action') || '#');
                ensureMethodPut(editForm);

                // Isi nilai text inputs
                if (eName) eName.value = btn.getAttribute('data-name') || '';
                if (eEmail) eEmail.value = btn.getAttribute('data-email') || '';
                if (ePwd) ePwd.value = '';
                if (ePwd2) ePwd2.value = '';

                // Roles (data-roles berupa JSON array)
                let roles = [];
                try {
                    roles = JSON.parse(btn.getAttribute('data-roles') || '[]');
                } catch(e) { roles = []; }
                setMultiSelect(eRoles, roles);

                // Active role dibatasi hanya pada roles yang dipilih
                const currentActive = btn.getAttribute('data-active') || '';
                rebuildActiveRoleOptions(eActive, roles, currentActive);

                open(modalEdit);
            });
        });

        if (modalEdit) {
            modalEdit.addEventListener('click', (e)=> { if (e.target.dataset.close) close(modalEdit); });
            document.addEventListener('keydown', (e)=> { if (e.key === 'Escape') close(modalEdit); });
        }

        // Jika user merubah pilihan roles di form (create/edit), sinkronkan pilihan active_role agar tetap valid
        function syncActiveOnRolesChange(container) {
            if (!container) return;
            const rolesSelect = container.querySelector('#roles');
            const activeSelect = container.querySelector('#active_role');
            if (!rolesSelect || !activeSelect) return;

            rolesSelect.addEventListener('change', () => {
                const selected = Array.from(rolesSelect.options).filter(o=>o.selected).map(o=>o.value);
                const currentActive = activeSelect.value;
                rebuildActiveRoleOptions(activeSelect, selected, selected.includes(currentActive) ? currentActive : '');
            });
        }
        syncActiveOnRolesChange(modalCreate);
        syncActiveOnRolesChange(modalEdit);

    })();
    </script>
    @endpush
</x-app-layout>