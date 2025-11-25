{{-- resources/views/admin/pages/courses/assign-students.blade.php --}}
<x-app-layout :title="'Assign Students — ' . $course->title">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-dark leading-tight">
                Assign Students — {{ $course->title }}
            </h2>

        </div>
    </x-slot>
    <a href="{{ route('instructor.courses.index') }}"
        class="inline-flex items-center justify-center font-semibold rounded-lg border px-4 py-2 text-sm text-dark hover:bg-soft">
        ← Back to Course Management
    </a>

    <div class="">
        <div class="mx-auto max-w-7xl">

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

            {{-- Course summary --}}
            <x-ui.card class="mb-4 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="text-sm text-dark/60">Course</div>
                        <div class="text-lg font-semibold text-dark">{{ $course->title }}</div>
                        <div class="text-xs text-dark/60 mt-1 flex items-center gap-4">
                            <span>
                                Status:
                                @if($course->status === 'published')
                                <x-ui.badge color="brand">Published</x-ui.badge>
                                @elseif($course->status === 'archived')
                                <x-ui.badge color="danger">Archived</x-ui.badge>
                                @else
                                <x-ui.badge color="gray">Draft</x-ui.badge>
                                @endif
                            </span>
                            @if($course->using_due_date)
                            <span class="text-blue-600 font-medium">
                                <x-ui.badge color="brand">Uses Due Date</x-ui.badge>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-dark/60">Total Enrolled</div>
                        <div class="text-2xl font-bold text-dark">
                            {{ number_format($course->enrollments()->count()) }}
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- LEFT: Available Students (bulk assign) --}}
                <x-ui.card class="p-0 overflow-hidden">
                    <div class="p-4 border-b border-soft flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-dark">Available Students</div>
                            <div class="text-xs text-dark/60">Belum terdaftar di kursus ini</div>
                        </div>
                        <form method="GET" class="flex items-center gap-2">
                            <x-ui.input name="q" :value="$q" placeholder="Search name or email" />
                            <x-ui.button type="submit" color="brand">Search</x-ui.button>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('instructor.courses.assign.store', $course->id) }}" x-data="{
                            showModal: false,
                            selectedUsers: [],
                            isDueDateCourse: {{ $course->using_due_date ? 'true' : 'false' }},

                            handleAssignClick() {
                                // 1. Kumpulkan semua user yang tercentang
                                const checkedUsers = document.querySelectorAll('.chk-user:checked');
                                if (checkedUsers.length === 0) {
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire('Error', 'Pilih minimal satu student.', 'error');
                                    } else {
                                        alert('Pilih minimal satu student.');
                                    }
                                    return;
                                }

                                // 2. Jika course TIDAK pakai due date, langsung submit form
                                if (!this.isDueDateCourse) {
                                    this.$refs.assignForm.submit();
                                    return;
                                }

                                // 3. Jika pakai due date, siapkan data untuk modal
                                this.selectedUsers = [];
                                checkedUsers.forEach(cb => {
                                    this.selectedUsers.push({
                                        id: cb.value,
                                        name: cb.dataset.name
                                    });
                                });

                                // 4. Tampilkan modal
                                this.showModal = true;
                            },

                            // ====== 2. FUNGSI VALIDASI CLIENT-SIDE ======
                            validateAndSubmitDueDateForm() {
                                let allValid = true;

                                for (const user of this.selectedUsers) {
                                    const startDateInput = this.$refs.assignForm.querySelector(`#start_date_${user.id}`);
                                    const endDateInput = this.$refs.assignForm.querySelector(`#end_date_${user.id}`);

                                    // Reset style error
                                    startDateInput.style.borderColor = '';
                                    endDateInput.style.borderColor = '';

                                    // Validasi start_date
                                    if (!startDateInput.value) {
                                        allValid = false;
                                        startDateInput.style.borderColor = 'red';
                                    }

                                    // Validasi end_date
                                    if (!endDateInput.value) {
                                        allValid = false;
                                        endDateInput.style.borderColor = 'red';
                                    }
                                }

                                if (allValid) {
                                    // Jika semua valid, submit form
                                    this.$refs.assignForm.submit();
                                } else {
                                    // Jika ada yang kosong, tampilkan alert
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire('Input Required', 'Semua Start Date dan End Date wajib diisi.', 'error');
                                    } else {
                                        alert('Semua Start Date dan End Date wajib diisi.');
                                    }
                                }
                            }
                        }" x-ref="assignForm">
                        @csrf
                        <div class="p-4 overflow-x-auto">
                            <table class="min-w-full text-sm text-left divide-y divide-soft">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2">
                                            <input type="checkbox" id="check_all_available" class="rounded border-soft"
                                                onclick="document.querySelectorAll('.chk-user').forEach(cb => cb.checked = this.checked)">
                                        </th>
                                        <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Name</th>
                                        <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Email</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse($available as $u)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">
                                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                                                data-name="{{ $u->name }}" class="chk-user rounded border-soft">
                                        </td>
                                        <td class="px-3 py-2 text-dark font-medium">{{ $u->name }}</td>
                                        <td class="px-3 py-2 text-dark/70">{{ $u->email }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-6 text-center text-sm text-gray-500">
                                            Tidak ada data.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <div class="mt-3">
                                {{ $available->links() }}
                            </div>
                        </div>

                        <div class="p-4 border-t border-soft flex items-center justify-between">
                            <div class="text-xs text-dark/60">Pilih beberapa lalu klik “Assign Selected”.</div>
                            <x-ui.button type="button" color="brand" @click="handleAssignClick()">
                                Assign Selected
                            </x-ui.button>
                        </div>


                        <div x-show="showModal" style="display: none;" @keydown.escape.window="showModal = false"
                            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                            aria-modal="true">
                            <div
                                class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                                    @click="showModal = false" aria-hidden="true"></div>

                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                    aria-hidden="true">&#8203;</span>
                                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">

                                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                        Set Due Dates
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Kursus ini mewajibkan Start Date dan End Date untuk setiap pendaftaran.
                                    </p>

                                    <div class="mt-4 space-y-4 max-h-96 overflow-y-auto p-1">
                                        <template x-for="user in selectedUsers" :key="user.id">
                                            <div class="p-3 border rounded-lg bg-gray-50">
                                                <strong class="block text-sm font-medium text-gray-900"
                                                    x-text="user.name"></strong>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
                                                    <div>
                                                        <label :for="'start_date_' + user.id"
                                                            class="block text-xs font-medium text-gray-600">Start
                                                            Date</label>
                                                        <input type="date" :id="'start_date_' + user.id"
                                                            :name="'due_dates[' + user.id + '][start_date]'"
                                                            class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-brand focus:border-brand"
                                                            required>
                                                    </div>
                                                    <div>
                                                        <label :for="'end_date_' + user.id"
                                                            class="block text-xs font-medium text-gray-600">End
                                                            Date</label>
                                                        <input type="date" :id="'end_date_' + user.id"
                                                            :name="'due_dates[' + user.id + '][end_date]'"
                                                            class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-brand focus:border-brand"
                                                            required>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="mt-6 sm:flex sm:flex-row-reverse">
                                        <x-ui.button type="button" color="brand" class="w-full sm:w-auto sm:ml-3"
                                            @click="validateAndSubmitDueDateForm()">
                                            Assign & Set Dates
                                        </x-ui.button>
                                        <x-ui.button type="button" color="gray" @click="showModal = false"
                                            class="w-full mt-3 sm:w-auto sm:mt-0">
                                            Cancel
                                        </x-ui.button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </x-ui.card>

                {{-- RIGHT: Enrolled Students (from enrollments) --}}
                <x-ui.card class="p-0 overflow-hidden">
                    <div class="p-4 border-b border-soft">
                        <div class="text-sm font-semibold text-dark">Enrolled Students</div>
                        <div class="text-xs text-dark/60">Status dari tabel enrollments</div>
                    </div>

                    <div class="p-4 overflow-x-auto">
                        <table class="min-w-full text-sm text-left divide-y divide-soft">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Name</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Email</th>
                                    @if($course->using_due_date)
                                        <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Start Date</th>
                                        <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">End Date</th>
                                    @endif
                                    <th class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 uppercase">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($enrolled as $en)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-dark font-medium">{{ $en->user->name }}</td>
                                    <td class="px-3 py-2 text-dark/70">{{ $en->user->email }}</td>
                                    @if($course->using_due_date)
                                        <td class="px-3 py-2 text-dark/70">
                                            {{ $en->dueDate ? \Carbon\Carbon::parse($en->dueDate->start_date)->format('d M Y') : '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-dark/70">
                                            {{ $en->dueDate ? \Carbon\Carbon::parse($en->dueDate->end_date)->format('d M Y') : '-' }}
                                        </td>
                                    @endif
                                    <td class="px-3 py-2">
                                        @if($en->status === 'active')
                                        <x-ui.badge color="brand">Active</x-ui.badge>
                                        @elseif($en->status === 'completed')
                                        <x-ui.badge color="accent">Completed</x-ui.badge>
                                        @elseif($en->status === 'assigned')
                                        <x-ui.badge color="gray">Assigned</x-ui.badge>
                                        @else
                                        <x-ui.badge color="gray">Cancelled</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <form
                                            action="{{ route('instructor.courses.assign.remove', [$course->id, $en->user->id]) }}"
                                            method="POST" class="inline remove-student-form"
                                            onsubmit="return confirmRemoveStudent(event, this, '{{ $en->user->name }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center font-semibold rounded-lg px-3 py-1.5 text-sm bg-danger text-white hover:brightness-95">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="ml-1">Remove</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">
                                        Belum ada mahasiswa terdaftar.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $enrolled->appends(['q'=>request('q')])->links() }}
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Global helper: confirm with SweetAlert2 if available, native confirm as fallback
        function confirmRemoveStudent(e, form, name) {
            if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Remove Student?',
                    text: `Hapus ${name || 'student'} dari kursus ini?`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        HTMLFormElement.prototype.submit.call(form);
                    }
                });
                return false;
            }

            // Fallback native confirm
            if (!confirm(`Hapus ${name || 'student'} dari kursus ini?`)) {
                e.preventDefault();
                return false;
            }
            return true;
        }
    </script>
    @endpush
</x-app-layout>