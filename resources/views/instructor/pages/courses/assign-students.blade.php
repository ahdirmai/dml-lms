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
                        <div class="text-xs text-dark/60 mt-1">
                            Status:
                            @if($course->status === 'published')
                            <x-ui.badge color="brand">Published</x-ui.badge>
                            @elseif($course->status === 'archived')
                            <x-ui.badge color="danger">Archived</x-ui.badge>
                            @else
                            <x-ui.badge color="gray">Draft</x-ui.badge>
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

                    <form method="POST" action="{{ route('instructor.courses.assign.store', $course->id) }}">
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
                                                class="chk-user rounded border-soft">
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
                            <x-ui.button type="submit" color="brand">Assign Selected</x-ui.button>
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
                                                Remove
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
