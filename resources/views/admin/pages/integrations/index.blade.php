{{-- resources/views/admin/integration/index.blade.php --}}
<x-app-layout :title="'Integrasi User'">
    <x-slot name="header">
        Integrasi User
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold">
                Integrasi User dari Sistem Internal
            </h1>
        </div>

        {{-- Alerts --}}
        <div id="integration-alert" class="mb-4 hidden">
            <div id="integration-alert-inner" class="px-4 py-3 rounded border text-sm">
            </div>
        </div>

        {{-- Filter & Preview --}}
        <div class="bg-white shadow rounded-lg mb-6 p-4">
            <form id="integration-filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf

                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700">
                        Department
                    </label>
                    <input type="text" name="department" id="department"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        Status
                    </label>
                    <select name="status" id="status"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Semua</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="resigned">Resigned</option>
                        <option value="terminated">Terminated</option>
                    </select>
                </div>

                <div>
                    <label for="limit" class="block text-sm font-medium text-gray-700">
                        Limit
                    </label>
                    <input type="number" name="limit" id="limit" value="100" min="1" max="1000"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>

                <div class="flex items-end justify-end">
                    <button type="button" id="btn-preview" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md
                                   text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                        Preview
                    </button>
                </div>
            </form>
        </div>

        {{-- Hidden state --}}
        <input type="hidden" id="import_session_id" value="">

        {{-- Result & Import --}}
        <div class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold">
                    Hasil Preview User
                </h2>
                <div class="flex items-center space-x-2">
                    <input type="text" id="preview-search" placeholder="Search user..."
                        class="border border-gray-300 rounded-md text-sm px-2 py-1">
                    <span id="preview-count" class="text-sm text-gray-600">
                        0 user
                    </span>
                    <button type="button" id="btn-import" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md
                               text-white bg-green-600 hover:bg-green-700 focus:outline-none disabled:opacity-50"
                        disabled>
                        Import Selected
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm" id="preview-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 border-b">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th class="px-3 py-2 border-b text-left">External ID</th>
                            <th class="px-3 py-2 border-b text-left">Nama</th>
                            <th class="px-3 py-2 border-b text-left">Email</th>
                            <th class="px-3 py-2 border-b text-left">Department</th>
                            <th class="px-3 py-2 border-b text-left">Job Title</th>
                            <th class="px-3 py-2 border-b text-left">Role</th>
                            <th class="px-3 py-2 border-b text-left">Status</th>
                            <th class="px-3 py-2 border-b text-left">Existing</th>
                        </tr>
                    </thead>
                    <tbody id="preview-tbody">
                        <tr>
                            <td colspan="9" class="px-3 py-4 text-center text-gray-500">
                                Belum ada data. Silakan lakukan Preview.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
                const previewUrl = "{{ route('admin.integration.users.preview') }}";
                const importUrl  = "{{ route('admin.integration.users.import') }}";
                const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const btnPreview     = document.getElementById('btn-preview');
                const btnImport      = document.getElementById('btn-import');
                const sessionInput   = document.getElementById('import_session_id');
                const tbody          = document.getElementById('preview-tbody');
                const previewCount   = document.getElementById('preview-count');
                const selectAll      = document.getElementById('select-all');
                const alertBox       = document.getElementById('integration-alert');
                const alertInner     = document.getElementById('integration-alert-inner');
                const searchInput    = document.getElementById('preview-search'); // <-- baru

                function setAlert(type, message) {
                    if (!alertBox || !alertInner) return;

                    alertBox.classList.remove('hidden');

                    const baseClasses = 'px-4 py-3 rounded border text-sm ';
                    let typeClasses = '';

                    switch (type) {
                        case 'success':
                            typeClasses = 'bg-green-50 border-green-400 text-green-800';
                            break;
                        case 'error':
                            typeClasses = 'bg-red-50 border-red-400 text-red-800';
                            break;
                        case 'warning':
                            typeClasses = 'bg-yellow-50 border-yellow-400 text-yellow-800';
                            break;
                        default:
                            typeClasses = 'bg-gray-50 border-gray-300 text-gray-800';
                    }

                    alertInner.className = baseClasses + typeClasses;
                    alertInner.textContent = message;
                }

                function clearAlert() {
                    if (!alertBox) return;
                    alertBox.classList.add('hidden');
                    alertInner.textContent = '';
                }

                function setLoading(button, isLoading) {
                    if (!button) return;
                    button.disabled = isLoading;
                    if (isLoading) {
                        button.dataset.originalText = button.textContent;
                        button.textContent = 'Processing...';
                    } else if (button.dataset.originalText) {
                        button.textContent = button.dataset.originalText;
                        delete button.dataset.originalText;
                    }
                }

                function renderTable(data) {
                    tbody.innerHTML = '';

                    if (!data || !data.length) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="9" class="px-3 py-4 text-center text-gray-500">
                                    Tidak ada data ditemukan.
                                </td>
                            </tr>
                        `;
                        previewCount.textContent = '0 user';
                        btnImport.disabled = true;
                        return;
                    }

                    data.forEach(function (row, index) {
                        const tr = document.createElement('tr');
                        tr.className = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';

                        const roles = [];
                        if (row.is_employee) roles.push('Student');
                        if (row.is_hr) roles.push('HR');

                        tr.innerHTML = `
                            <td class="px-3 py-2 border-b">
                                <input type="checkbox" class="row-check"
                                       data-external-id="${row.external_id ?? ''}">
                            </td>
                            <td class="px-3 py-2 border-b whitespace-nowrap">${row.external_id ?? ''}</td>
                            <td class="px-3 py-2 border-b">${row.full_name ?? ''}</td>
                            <td class="px-3 py-2 border-b">${row.email ?? ''}</td>
                            <td class="px-3 py-2 border-b">${row.department ?? ''}</td>
                            <td class="px-3 py-2 border-b">${row.job_title ?? ''}</td>
                            <td class="px-3 py-2 border-b">${roles.join(', ') || '-'}</td>
                            <td class="px-3 py-2 border-b">${row.status ?? ''}</td>
                            <td class="px-3 py-2 border-b">
                                ${
                                    row.already_exists
                                        ? '<span class="inline-flex px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Sudah ada</span>'
                                        : '<span class="inline-flex px-2 py-1 text-xs rounded bg-green-100 text-green-800">Baru</span>'
                                }
                            </td>
                        `;

                        tbody.appendChild(tr);
                    });

                    previewCount.textContent = data.length + ' user';
                    btnImport.disabled = false;
                }

                function getSelectedChecks() {
                    return tbody.querySelectorAll('.row-check:checked');
                }

                function getSelectedExternalIds() {
                    const checks = getSelectedChecks();
                    const ids = [];
                    checks.forEach(function (chk) {
                        const id = chk.getAttribute('data-external-id');
                        if (id) ids.push(id);
                    });
                    return ids;
                }

                // Preview handler
                btnPreview?.addEventListener('click', function () {
                    clearAlert();
                    setLoading(btnPreview, true);
                    btnImport.disabled = true;
                    sessionInput.value = '';
                    selectAll.checked = false;

                    const form = document.getElementById('integration-filter-form');
                    const formData = new FormData(form);

                    const payload = {
                        department: formData.get('department') || null,
                        status: formData.get('status') || null,
                        limit: formData.get('limit') || null,
                    };

                    fetch(previewUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    })
                        .then(async (res) => {
                            const json = await res.json();
                            if (!res.ok || !json.success) {
                                throw new Error(json.message || 'Gagal melakukan preview.');
                            }
                            sessionInput.value = json.import_session_id || '';
                            renderTable(json.data || []);
                        })
                        .catch((err) => {
                            console.error(err);
                            setAlert('error', err.message || 'Gagal melakukan preview.');
                        })
                        .finally(() => {
                            setLoading(btnPreview, false);
                        });
                });

                // Import handler
                btnImport?.addEventListener('click', function () {
                    clearAlert();

                    const sessionId = sessionInput.value;
                    if (!sessionId) {
                        setAlert('error', 'Session import tidak ditemukan. Silakan lakukan preview ulang.');
                        return;
                    }

                    const selectedChecks = getSelectedChecks();
                    const selectedIds = [];
                    selectedChecks.forEach((chk) => {
                        const id = chk.getAttribute('data-external-id');
                        if (id) selectedIds.push(id);
                    });

                    if (!selectedIds.length) {
                        setAlert('warning', 'Tidak ada user yang dipilih.');
                        return;
                    }

                    if (!confirm('Yakin akan mengimport ' + selectedIds.length + ' user terpilih?')) {
                        return;
                    }

                    setLoading(btnImport, true);

                    const payload = {
                        import_session_id: sessionId,
                        selected_external_ids: selectedIds,
                    };

                    fetch(importUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    })
                        .then(async (res) => {
                            const json = await res.json();
                            if (!res.ok || !json.success) {
                                throw new Error(json.message || 'Gagal melakukan import.');
                            }

                            const summary = json.summary || {};
                            const msg = [
                                'Import selesai.',
                                'Created: ' + (summary.created ?? 0),
                                'Updated: ' + (summary.updated ?? 0),
                                'Skipped: ' + (summary.skipped ?? 0),
                                'Failed: ' + (summary.failed ?? 0),
                            ].join(' ');

                            // UPDATE kolom Existing untuk baris yang di-import
                            selectedChecks.forEach((chk) => {
                                const row = chk.closest('tr');
                                if (!row) return;
                                const existingCell = row.querySelector('td:last-child');
                                if (!existingCell) return;

                                existingCell.innerHTML =
                                    '<span class="inline-flex px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Sudah ada</span>';

                                // optional: uncheck setelah selesai
                                chk.checked = false;
                            });

                            // optional: reset "select all"
                            if (selectAll) {
                                selectAll.checked = false;
                            }

                            setAlert('success', msg);
                        })
                        .catch((err) => {
                            console.error(err);
                            setAlert('error', err.message || 'Gagal melakukan import.');
                        })
                        .finally(() => {
                            setLoading(btnImport, false);
                        });
                });

                // Select all handler
                selectAll?.addEventListener('change', function () {
                    const checks = tbody.querySelectorAll('.row-check');
                    checks.forEach(function (chk) {
                        chk.checked = selectAll.checked;
                    });
                });

                // Keep "select all" in sync
                tbody.addEventListener('change', function (e) {
                    if (!e.target.classList.contains('row-check')) return;

                    const checks = tbody.querySelectorAll('.row-check');
                    const checked = tbody.querySelectorAll('.row-check:checked');

                    if (checked.length === checks.length && checks.length > 0) {
                        selectAll.checked = true;
                    } else {
                        selectAll.checked = false;
                    }
                });

                // SEARCH hasil preview (client-side)
                searchInput?.addEventListener('input', function () {
                    const term = this.value.trim().toLowerCase();
                    const rows = tbody.querySelectorAll('tr');

                    // jika tidak ada data sama sekali
                    if (!rows.length) return;

                    let visibleCount = 0;

                    rows.forEach((row) => {
                        const cells = row.querySelectorAll('td');

                        // row "Tidak ada data ditemukan."
                        if (!cells.length || cells[0].colSpan === 9) {
                            row.style.display = term ? 'none' : '';
                            return;
                        }

                        const text = row.textContent.toLowerCase();
                        const match = !term || text.includes(term);

                        row.style.display = match ? '' : 'none';

                        if (match) {
                            visibleCount++;
                        }
                    });

                    // update counter
                    if (!term) {
                        const total = tbody.querySelectorAll('.row-check').length;
                        previewCount.textContent = total + ' user';
                    } else {
                        previewCount.textContent = visibleCount + ' user (filtered)';
                    }
                });
            })();
    </script>
    @endpush
</x-app-layout>