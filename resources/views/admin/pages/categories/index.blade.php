<x-app-layout :title="'Categories'">
    <x-slot name="header">
        Categories
    </x-slot>

    <div class="py-4">
        <div class="mx-auto">

            {{-- Alerts --}}
            @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
            @endif

            {{-- Top actions --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                {{-- Filter/Search (opsional; hapus jika tak ada) --}}
                <form method="GET" class="flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-[1fr,auto] gap-2">
                        <x-ui.input name="q" :value="request('q')" placeholder="Search categories..." />
                        <x-ui.button type="submit" color="brand" class="sm:w-36">Search</x-ui.button>
                    </div>
                </form>

                {{-- Create button --}}
                <div class="md:text-right">
                    <button type="button" id="btnOpenCreate" class="inline-flex items-center justify-center font-semibold rounded-lg transition
                                   focus:outline-none focus:ring-2 focus:ring-offset-2 px-4 py-2 text-sm bg-brand text-white
                                   hover:brightness-95 focus:ring-brand w-full md:w-auto">
                        + Create Category
                    </button>
                </div>
            </div>

            {{-- ===== TABLE (Desktop/Tablet) ===== --}}
            <div class="bg-white shadow sm:rounded-lg hidden md:block">
                <div class="p-4 overflow-x-auto">
                    <x-ui.table class="min-w-full text-sm text-left">
                        <x-ui.thead>
                            <tr>
                                <x-ui.th>Name</x-ui.th>
                                <x-ui.th>Slug</x-ui.th>
                                <x-ui.th>Description</x-ui.th>
                                <x-ui.th class="text-right">Action</x-ui.th>
                            </tr>
                        </x-ui.thead>
                        <x-ui.tbody>
                            @forelse ($categories as $cat)
                            <tr class="hover:bg-gray-50">
                                <x-ui.td class="font-semibold text-dark">{{ $cat->name }}</x-ui.td>
                                <x-ui.td class="text-dark/70">{{ $cat->slug ?? '—' }}</x-ui.td>
                                <x-ui.td class="text-dark/70">
                                    {{ \Illuminate\Support\Str::limit($cat->description, 120) ?? '—' }}
                                </x-ui.td>
                                <x-ui.td class="text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" 
                                            class="p-2 text-gray-400 hover:text-brand hover:bg-brand/5 rounded-lg transition-colors btn-edit"
                                            title="Edit Category"
                                            data-id="{{ $cat->id }}" 
                                            data-name="{{ $cat->name }}"
                                            data-slug="{{ $cat->slug }}" 
                                            data-description="{{ $cat->description }}"
                                            data-action="{{ route('admin.categories.update', $cat->id) }}">
                                            <x-ui.icon name="pencil" class="w-4 h-4" />
                                        </button>

                                        <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Delete this category?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Category">
                                                <x-ui.icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </x-ui.td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">
                                    <x-ui.empty-state title="No categories" />
                                </td>
                            </tr>
                            @endforelse
                        </x-ui.tbody>
                    </x-ui.table>

                    <div class="mt-4">{{ $categories->withQueryString()->links() }}</div>
                </div>
            </div>

            {{-- ===== CARD LIST (Mobile) ===== --}}
            <div class="space-y-3 md:hidden">
                @forelse($categories as $cat)
                <x-ui.card class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-dark text-base truncate">{{ $cat->name }}</h3>
                            <p class="text-xs text-dark/60 truncate">slug: {{ $cat->slug ?? '—' }}</p>
                            @if($cat->description)
                            <p class="mt-1 text-xs text-dark/70 line-clamp-2">{{ $cat->description }}</p>
                            @endif
                        </div>
                        <div class="shrink-0">
                            <span
                                class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-soft text-dark/70 border border-soft">
                                Category
                            </span>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button"
                            class="flex-1 min-w-[7rem] inline-flex items-center justify-center font-semibold rounded-lg border px-3 py-2 text-sm text-dark hover:bg-soft btn-edit"
                            data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" data-slug="{{ $cat->slug }}"
                            data-description="{{ $cat->description }}"
                            data-action="{{ route('admin.categories.update', $cat->id) }}">
                            Edit
                        </button>
                        <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST"
                            class="flex-1 min-w-[7rem]" onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center font-semibold rounded-lg px-3 py-2 text-sm bg-danger text-white hover:brightness-95">
                                Delete
                            </button>
                        </form>
                    </div>
                </x-ui.card>
                @empty
                <x-ui.card class="p-6 text-center text-sm text-gray-500">No categories</x-ui.card>
                @endforelse

                <div class="mt-4">{{ $categories->withQueryString()->links() }}</div>
            </div>

        </div>
    </div>

    {{-- ================== MODALS ================== --}}

    {{-- Create Modal --}}
    <div id="modalCreate" class="fixed inset-0 z-40 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="true"></div>
        <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2
                    w-full sm:w-[32rem] bg-white rounded-t-2xl sm:rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="font-semibold text-dark">Create Category</h3>
                <button type="button" class="p-2 rounded hover:bg-soft" data-close="true" aria-label="Close">✕</button>
            </div>
            <div class="p-4">
                @include('admin.pages.categories._form', [
                'action' => route('admin.categories.store'),
                'method' => 'POST',
                // jangan kirim $category di create
                ])
            </div>
        </div>
    </div>

    {{-- Edit Modal (single, diisi via JS) --}}
    <div id="modalEdit" class="fixed inset-0 z-40 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="true"></div>
        <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2
                    w-full sm:w-[32rem] bg-white rounded-t-2xl sm:rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="font-semibold text-dark">Edit Category</h3>
                <button type="button" class="p-2 rounded hover:bg-soft" data-close="true" aria-label="Close">✕</button>
            </div>
            <div class="p-4">
                {{-- gunakan _form tapi action & values akan diisi via JS --}}
                @include('admin.pages.categories._form', [
                'action' => '#', // akan diubah JS
                'method' => 'POST', // _method akan disuntik PUT oleh JS
                // $category sengaja tidak dikirim; nilai akan diisi JS ke input
                ])
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
        // Helpers modal
        function openModal(el){ el.classList.remove('hidden'); document.documentElement.style.overflow='hidden'; }
        function closeModal(el){ el.classList.add('hidden'); document.documentElement.style.overflow=''; }

        // Create modal
        const modalCreate = document.getElementById('modalCreate');
        const btnOpenCreate = document.getElementById('btnOpenCreate');
        if (btnOpenCreate && modalCreate) {
            btnOpenCreate.addEventListener('click', ()=> openModal(modalCreate));
            modalCreate.addEventListener('click', (e)=> { if (e.target.dataset.close) closeModal(modalCreate); });
            document.addEventListener('keydown', (e)=> { if (e.key === 'Escape') closeModal(modalCreate); });
        }

        // Edit modal
        const modalEdit = document.getElementById('modalEdit');
        const editButtons = document.querySelectorAll('.btn-edit');

        // Grab the form & inputs inside edit modal (from _form)
        const editForm = modalEdit ? modalEdit.querySelector('form') : null;
        const inputName = modalEdit ? modalEdit.querySelector('#name') : null;
        const inputSlug = modalEdit ? modalEdit.querySelector('#slug') : null;
        const inputDesc = modalEdit ? modalEdit.querySelector('#description') : null;

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

        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (!modalEdit || !editForm) return;

                // set action target from data-action
                const action = btn.getAttribute('data-action') || '#';
                editForm.setAttribute('action', action);
                ensureMethodPut(editForm);

                // fill values
                if (inputName) inputName.value = btn.getAttribute('data-name') || '';
                if (inputSlug) inputSlug.value = btn.getAttribute('data-slug') || '';
                if (inputDesc) inputDesc.value = btn.getAttribute('data-description') || '';

                openModal(modalEdit);
            });
        });

        if (modalEdit) {
            modalEdit.addEventListener('click', (e)=> { if (e.target.dataset.close) closeModal(modalEdit); });
            document.addEventListener('keydown', (e)=> { if (e.key === 'Escape') closeModal(modalEdit); });
        }
    })();
    </script>
    @endpush

</x-app-layout>
