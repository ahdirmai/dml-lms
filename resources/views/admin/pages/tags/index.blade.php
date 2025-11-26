<x-app-layout :title="'Tags Management'">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-bold text-xl text-dark leading-tight">Tags Management</h2>
            <p class="text-sm text-dark/60">Manage content tags for better organization.</p>
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

            {{-- Toolbar --}}
            <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    {{-- Search --}}
                    <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto flex-1">
                        <div class="relative w-full sm:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-ui.icon name="search" class="h-4 w-4 text-gray-400" />
                            </div>
                            <x-ui.input type="text" name="q" value="{{ $q ?? request('q') }}"
                                placeholder="Search tags..." class="pl-9 w-full" />
                        </div>
                        <x-ui.button type="submit" variant="secondary" class="whitespace-nowrap">
                            <x-ui.icon name="search" class="w-4 h-4 mr-2" /> Search
                        </x-ui.button>
                    </form>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                        <x-ui.button type="button" id="btnOpenCreate" variant="primary" class="whitespace-nowrap shadow-md shadow-brand/20">
                            <x-ui.icon name="plus" class="w-4 h-4 mr-2" /> New Tag
                        </x-ui.button>
                    </div>
                </div>
            </div>

            {{-- Result Count --}}
            <div class="text-sm text-gray-500">
                Showing <span class="font-semibold text-gray-900">{{ $tags->firstItem() ?? 0 }}</span> - 
                <span class="font-semibold text-gray-900">{{ $tags->lastItem() ?? 0 }}</span> 
                of <span class="font-semibold text-gray-900">{{ $tags->total() }}</span> tags
            </div>

            {{-- DESKTOP TABLE VIEW --}}
            <div class="bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden hidden md:block">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Slug</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($tags as $t)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $t->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $t->slug }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" 
                                            class="p-2 text-gray-400 hover:text-brand hover:bg-brand/5 rounded-lg transition-colors btn-edit"
                                            title="Edit Tag"
                                            data-action="{{ route('admin.tags.update', $t) }}"
                                            data-name="{{ $t->name }}"
                                            data-slug="{{ $t->slug }}">
                                            <x-ui.icon name="pencil" class="w-4 h-4" />
                                        </button>
                                        <form action="{{ route('admin.tags.destroy', $t) }}" method="POST" onsubmit="return confirm('Delete this tag?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Tag">
                                                <x-ui.icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-ui.icon name="tag" class="w-12 h-12 text-gray-300 mb-3" />
                                        <p class="text-lg font-medium text-gray-900">No tags found</p>
                                        <p class="text-sm text-gray-500 mb-4">Start by creating a new tag.</p>
                                        <x-ui.button type="button" id="btnOpenCreateInline" variant="primary">
                                            Create New Tag
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MOBILE CARD VIEW --}}
            <div class="md:hidden space-y-4">
                @forelse($tags as $t)
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $t->name }}</h3>
                            <code class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded mt-1 inline-block">{{ $t->slug }}</code>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4 pt-3 border-t border-gray-100">
                        <button type="button" 
                           class="flex-1 inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-2 text-sm text-dark hover:bg-soft btn-edit"
                           data-action="{{ route('admin.tags.update', $t) }}"
                           data-name="{{ $t->name }}"
                           data-slug="{{ $t->slug }}">
                            Edit
                        </button>
                        <form action="{{ route('admin.tags.destroy', $t) }}" method="POST" class="flex-1" onsubmit="return confirm('Delete this tag?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full inline-flex items-center justify-center font-semibold rounded-lg px-3 py-2 text-sm bg-danger text-white hover:brightness-95">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-8 text-center">
                    <x-ui.icon name="tag" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-lg font-medium text-gray-900">No tags found</p>
                    <x-ui.button type="button" id="btnOpenCreateMobile" variant="primary" class="mt-4">
                        Create New Tag
                    </x-ui.button>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($tags->hasPages())
            <div class="mt-4">
                {{ $tags->withQueryString()->links() }}
            </div>
            @endif

        </div>
    </div>

    {{-- ================== MODALS ================== --}}

    {{-- Create Modal --}}
    <div id="modalCreate" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" data-close="true"></div>
        <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2
                    w-full sm:w-[32rem] bg-white sm:rounded-2xl shadow-2xl overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-lg text-dark">Create New Tag</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" data-close="true">
                    <x-ui.icon name="x" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-6 max-h-[85vh] overflow-y-auto">
                @include('admin.pages.tags._form', [
                    'action' => route('admin.tags.store'),
                    'method' => 'POST',
                    'tag' => null,
                    'idPrefix' => 'create_'
                ])
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="modalEdit" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" data-close="true"></div>
        <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2
                    w-full sm:w-[32rem] bg-white sm:rounded-2xl shadow-2xl overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-lg text-dark">Edit Tag</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" data-close="true">
                    <x-ui.icon name="x" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-6 max-h-[85vh] overflow-y-auto">
                @include('admin.pages.tags._form', [
                    'action' => '#',
                    'method' => 'POST',
                    'tag' => null,
                    'idPrefix' => 'edit_'
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
                
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(modal);
                });
            });

            // ===== Create Modal Triggers
            const modalCreate = document.getElementById('modalCreate');
            const createForm = modalCreate?.querySelector('form');
            const cName = modalCreate?.querySelector('#create_name');
            const cSlug = modalCreate?.querySelector('#create_slug');

            function clearCreateForm() {
                if (!createForm) return;
                createForm.action = "{{ route('admin.tags.store') }}";
                
                const m = createForm.querySelector('input[name="_method"]');
                if (m) m.remove();
                
                createForm.reset();
                if(cName) cName.value = '';
                if(cSlug) cSlug.value = '';
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
            const eName = modalEdit?.querySelector('#edit_name');
            const eSlug = modalEdit?.querySelector('#edit_slug');

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
                    if (eSlug) eSlug.value = btn.dataset.slug || '';

                    open(modalEdit);
                });
            });

        })();
    </script>
    @endpush
</x-app-layout>
