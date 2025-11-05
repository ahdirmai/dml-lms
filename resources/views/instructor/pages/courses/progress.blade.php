{{-- resources/views/admin/pages/courses/progress.blade.php --}}
<x-app-layout :title="'Progress — ' . $course->title">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-dark leading-tight">
                Progress Tracking — {{ $course->title }}
            </h2>

        </div>
    </x-slot>

    <a href="{{ route('admin.courses.index') }}"
        class="inline-flex items-center justify-center font-semibold rounded-lg border px-4 py-2 text-sm text-dark hover:bg-soft">
        ← Back to Course Management
    </a>
    @php
    /**
    * ============================
    * DUMMY DATA (sementara)
    * ============================
    *
    * TODO (REAL DATA - gunakan Eloquent):
    * - $enrollments = $course->enrollments()
    * ->with(['user:id,name,email', 'course'])
    * ->withCount(['course as lessons_total' => fn($q) => $q->selectRaw('(select count(*) from lessons where
    lessons.module_id in (...))')]) // contoh
    * ->paginate(10);
    *
    * - $moduleBreakdown = $course->modules()
    * ->withCount('lessons')
    * ->with(['lessons' => fn($q) => $q->select('id','module_id','title')])
    * ->get();
    *
    * - $progressByUser = LessonProgress::whereIn('enrollment_id', $enrollments->pluck('id'))
    * ->selectRaw('enrollment_id, count(*) filter(where status="completed") as completed, count(*) as total')
    * ->groupBy('enrollment_id')->get();
    *
    * Catatan PRD (ringkas): simpan progress per lesson di tabel `lesson_progress` (not_started|in_progress|completed),
    * hitung persentase = completed/total * 100, tandai course completed => generate certificate. (PRD—Progress
    Tracking)
    */

    $summary = [
    'students_total' => 42,
    'students_active' => 31,
    'students_completed' => 9,
    'avg_progress' => 57, // %
    ];

    $moduleBreakdown = [
    ['id' => 1, 'title' => 'Introduction', 'lessons_total' => 5, 'lessons_completed' => 3],
    ['id' => 2, 'title' => 'Fundamentals', 'lessons_total' => 8, 'lessons_completed' => 4],
    ['id' => 3, 'title' => 'Advanced Topics', 'lessons_total' => 6, 'lessons_completed' => 2],
    ];

    $students = [
    [
    'name' => 'Asep Nugraha',
    'email' => 'asep@example.com',
    'status' => 'active', // assigned|active|completed|cancelled
    'progress' => 72,
    'completed_lessons' => 13,
    'total_lessons' => 18,
    'last_activity' => '2025-11-03 14:12',
    ],
    [
    'name' => 'Dewi Lestari',
    'email' => 'dewi@example.com',
    'status' => 'assigned',
    'progress' => 0,
    'completed_lessons' => 0,
    'total_lessons' => 18,
    'last_activity' => null,
    ],
    [
    'name' => 'Budi Santoso',
    'email' => 'budi@example.com',
    'status' => 'completed',
    'progress' => 100,
    'completed_lessons' => 18,
    'total_lessons' => 18,
    'last_activity' => '2025-11-02 09:44',
    ],
    [
    'name' => 'Siti Rahma',
    'email' => 'siti@example.com',
    'status' => 'active',
    'progress' => 35,
    'completed_lessons' => 6,
    'total_lessons' => 17,
    'last_activity' => '2025-11-04 10:01',
    ],
    ];
    @endphp

    <div class="">
        <div class="mx-auto max-w-7xl space-y-4">

            {{-- Alerts --}}
            @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <x-ui.card class="p-4">
                    <div class="text-xs uppercase text-dark/60">Total Students</div>
                    <div class="mt-1 text-2xl font-bold text-dark">{{ number_format($summary['students_total']) }}</div>
                </x-ui.card>
                <x-ui.card class="p-4">
                    <div class="text-xs uppercase text-dark/60">Active</div>
                    <div class="mt-1 text-2xl font-bold text-dark">{{ number_format($summary['students_active']) }}
                    </div>
                </x-ui.card>
                <x-ui.card class="p-4">
                    <div class="text-xs uppercase text-dark/60">Completed</div>
                    <div class="mt-1 text-2xl font-bold text-dark">{{ number_format($summary['students_completed']) }}
                    </div>
                </x-ui.card>
                <x-ui.card class="p-4">
                    <div class="text-xs uppercase text-dark/60">Average Progress</div>
                    <div class="mt-2">
                        <div class="w-full h-2 bg-soft rounded-full overflow-hidden">
                            <div class="h-2 bg-brand rounded-full" style="width: {{ $summary['avg_progress'] }}%"></div>
                        </div>
                        <div class="mt-1 text-sm font-semibold text-dark">{{ $summary['avg_progress'] }}%</div>
                    </div>
                </x-ui.card>
            </div>

            {{-- Filters --}}
            <x-ui.card class="p-4">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <x-ui.input name="q" :value="request('q')" placeholder="Search student name/email" />
                    <x-ui.select name="status">
                        <option value="">All Status</option>
                        <option value="assigned" @selected(request('status')==='assigned' )>Assigned</option>
                        <option value="active" @selected(request('status')==='active' )>Active</option>
                        <option value="completed" @selected(request('status')==='completed' )>Completed</option>
                        <option value="cancelled" @selected(request('status')==='cancelled' )>Cancelled</option>
                    </x-ui.select>
                    <x-ui.select name="progress_range">
                        <option value="">All Progress</option>
                        <option value="0-25" @selected(request('progress_range')==='0-25' )>0–25%</option>
                        <option value="25-50" @selected(request('progress_range')==='25-50' )>25–50%</option>
                        <option value="50-75" @selected(request('progress_range')==='50-75' )>50–75%</option>
                        <option value="75-100" @selected(request('progress_range')==='75-100' )>75–100%</option>
                    </x-ui.select>
                    <div class="flex items-center gap-2">
                        <x-ui.button type="submit" color="brand" class="w-full">Apply</x-ui.button>
                        <a href="{{ route('admin.courses.progress', $course->id ?? null) }}"
                            class="inline-flex items-center justify-center font-semibold rounded-lg border px-4 py-2 text-sm text-dark hover:bg-soft w-full">
                            Reset
                        </a>
                    </div>
                </form>
            </x-ui.card>

            {{-- Overall Module Breakdown --}}
            <x-ui.card class="p-0 overflow-hidden">
                <div class="p-4 border-b border-soft">
                    <div class="text-sm font-semibold text-dark">Module Breakdown</div>
                    <div class="text-xs text-dark/60">Ringkasan jumlah lesson selesai vs total per modul</div>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($moduleBreakdown as $m)
                    @php
                    $pct = $m['lessons_total'] > 0 ? round($m['lessons_completed'] / $m['lessons_total'] * 100) : 0;
                    @endphp
                    <div class="rounded-xl border border-soft p-4">
                        <div class="font-semibold text-dark">{{ $m['title'] }}</div>
                        <div class="text-xs text-dark/60 mb-2">
                            {{ $m['lessons_completed'] }} / {{ $m['lessons_total'] }} lessons
                        </div>
                        <div class="w-full h-2 bg-soft rounded-full overflow-hidden">
                            <div class="h-2 bg-accent rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="mt-1 text-sm font-medium text-dark">{{ $pct }}%</div>
                    </div>
                    @endforeach
                </div>
            </x-ui.card>

            {{-- Students Table --}}
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-4 border-b border-soft flex items-center justify-between">
                    <div class="text-sm text-dark/60">Student Progress</div>
                    {{-- Placeholder ekspor jika perlu --}}
                    <div class="flex items-center gap-2">
                        <x-ui.button type="button" class="bg-soft text-dark hover:bg-gray-100">Export CSV</x-ui.button>
                    </div>
                </div>

                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full text-sm text-left divide-y divide-soft">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Student
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Status
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Progress
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Completed
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Last
                                    Activity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($students as $s)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-dark font-medium">{{ $s['name'] }}</td>
                                <td class="px-3 py-2 text-dark/70">{{ $s['email'] }}</td>
                                <td class="px-3 py-2">
                                    @if($s['status'] === 'active')
                                    <x-ui.badge color="brand">Active</x-ui.badge>
                                    @elseif($s['status'] === 'completed')
                                    <x-ui.badge color="accent">Completed</x-ui.badge>
                                    @elseif($s['status'] === 'assigned')
                                    <x-ui.badge color="gray">Assigned</x-ui.badge>
                                    @else
                                    <x-ui.badge color="danger">Cancelled</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <div class="w-48">
                                        <div class="w-full h-2 bg-soft rounded-full overflow-hidden">
                                            <div class="h-2 bg-brand rounded-full" style="width: {{ $s['progress'] }}%">
                                            </div>
                                        </div>
                                        <div class="mt-1 text-xs font-medium text-dark">{{ $s['progress'] }}%</div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-dark/80">
                                    {{ $s['completed_lessons'] }} / {{ $s['total_lessons'] }}
                                </td>
                                <td class="px-3 py-2 text-dark/60">
                                    {{ $s['last_activity'] ? \Carbon\Carbon::parse($s['last_activity'])->diffForHumans()
                                    : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- TODO (REAL DATA): gunakan $enrollments->links() jika pagination --}}
                    {{-- <div class="mt-4">{{ $enrollments->withQueryString()->links() }}</div> --}}
                </div>
            </div>

            {{-- Notes / Align dengan PRD --}}
            <x-ui.card class="p-4">
                <div class="text-sm text-dark/80">
                    <div class="font-semibold mb-1">Catatan Implementasi (sesuai PRD — Progress Tracking)</div>
                    <ul class="list-disc list-inside text-dark/70 space-y-1">
                        <li>Progres dihitung dari jumlah <em>lesson_progress.status = completed</em> dibagi total lesson
                            kursus.</li>
                        <li>Jika semua lesson completed → ubah status enrollment ke <strong>completed</strong> dan
                            trigger generate certificate.</li>
                        <li>Akses hanya untuk student pemilik, instructor kursus, dan admin.</li>
                        <li>Gunakan event (LessonCompleted → Recalculate Course Progress) untuk update realtime.</li>
                    </ul>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>