<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\User;
use App\Models\Lms\Enrollment as LmsEnrollment;
use App\Models\Lms\EnrollmentDueDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class CourseAssignController extends Controller
{
    public function form(Request $request, Course $course)
    {
        $q = trim((string) $request->input('q', ''));

        // Daftar calon student (belum ter-enroll di course ini)
        $available = User::query()
            ->select('id', 'name', 'email')
            ->when(method_exists(User::class, 'roles'), function ($qq) {
                $qq->whereHas('roles', fn($r) => $r->where('name', 'student'));
            })
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->whereDoesntHave('enrollments', fn($w) => $w->where('course_id', $course->id))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Sudah enrolled
        $enrolled = $course->enrollments()
            ->select('enrollments.*')
            ->join('users', 'users.id', '=', 'enrollments.user_id')
            ->orderBy('users.name', 'asc')
            ->with('user:id,name,email')
            ->paginate(10, ['enrollments.*'], 'enrolled_page')
            ->withQueryString();

        return view('admin.pages.courses.assign-students', compact('course', 'available', 'enrolled', 'q'));
    }

    public function store(Request $request, Course $course)
    {
        // 2. Ambil status 'using_due_date' SEBELUM validasi
        $isDueDateCourse = $course->using_due_date;

        // 3. Tambahkan validasi kondisional
        $data = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],

            'due_dates' => [
                Rule::requiredIf(function () use ($course) {
                    return $course->using_due_date;
                }), // Wajib ada jika $isDueDateCourse true
                'nullable',
                'array'
            ],
            'due_dates.*.start_date' => [
                Rule::requiredIf(function () use ($course) {
                    return $course->using_due_date;
                }), // Wajib ada jika $isDueDateCourse true
                'nullable',
                'date'
            ],
            'due_dates.*.end_date' => [
                Rule::requiredIf(function () use ($course) {
                    return $course->using_due_date;
                }), // Wajib ada jika $isDueDateCourse true
                'nullable',
                'date',
                'after_or_equal:due_dates.*.start_date'
            ],
        ], [
            'user_ids.required' => 'Pilih minimal satu karyawan.',
            // 4. Tambahkan pesan error kustom
            'due_dates.required' => 'Pengaturan tanggal (due dates) wajib diisi untuk kursus ini.',
            'due_dates.*.start_date.required' => 'Start date wajib diisi untuk semua karyawan terpilih.',
            'due_dates.*.end_date.required' => 'End date wajib diisi untuk semua karyawan terpilih.',
            'due_dates.*.end_date.after_or_equal' => 'End date harus setelah atau sama dengan start date.'
        ]);

        try {
            DB::beginTransaction();

            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            $now  = now();
            $uids = collect($data['user_ids'])->unique()->values();

            $validUserIds = User::query()
                ->whereIn('id', $uids)
                ->pluck('id')
                ->all();

            if (empty($validUserIds)) {
                DB::rollBack();
                return back()->with('error', 'Tidak ada pengguna valid untuk di-assign.');
            }

            foreach ($validUserIds as $uid) {
                $enrollment = LmsEnrollment::updateOrCreate(
                    [
                        'user_id'   => $uid,
                        'course_id' => $fresh->id,
                    ],
                    [
                        'status'      => 'assigned',
                        'enrolled_at' => $now,
                        'updated_at'  => $now,
                    ]
                );

                // Cek data due_dates spesifik untuk user ini
                if ($fresh->using_due_date && isset($data['due_dates'][$uid])) {
                    $dates = $data['due_dates'][$uid];

                    // Validasi server-side sudah memastikan $dates['start_date'] dan $dates['end_date'] ada
                    EnrollmentDueDate::updateOrCreate(
                        ['enrollment_id' => $enrollment->id],
                        [
                            'start_date' => $dates['start_date'],
                            'end_date'   => $dates['end_date'],
                        ]
                    );
                }
            }

            DB::commit();

            return back()->with('success', 'Karyawan berhasil di-assign ke kursus.');
        } catch (Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat assign: ' . $e->getMessage());
        }
    }

    public function remove(Course $course, User $user)
    {
        try {
            DB::beginTransaction();

            // Kunci course agar konsisten
            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            // (Opsional) Validasi status kalau tidak boleh unassign saat published/ongoing
            // if ($fresh->status === Course::STATUS_PUBLISHED) {
            //     DB::rollBack();
            //     return back()->with('error', 'Tidak dapat menghapus peserta dari kursus yang sudah dipublikasikan.');
            // }

            $deleted = LmsEnrollment::query()
                ->where('course_id', $fresh->id)
                ->where('user_id', $user->id)
                ->delete();

            DB::commit();

            if ($deleted) {
                // DB::afterCommit(fn () => UnassignedFromCourse::dispatch($fresh->id, $user->id));
                return back()->with('success', 'Karyawan dihapus dari kursus.');
            }

            return back()->with('info', 'Karyawan tidak terdaftar pada kursus ini.');
        } catch (Throwable $e) {
            DB::rollBack();

            // Log::error('Gagal remove enrollment', ['course_id' => $course->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus karyawan dari kursus: ' . $e->getMessage());
        }
    }
}
