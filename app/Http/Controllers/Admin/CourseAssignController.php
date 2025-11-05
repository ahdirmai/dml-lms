<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\User;
use App\Models\Lms\Enrollment as LmsEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $data = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ], [
            'user_ids.required' => 'Pilih minimal satu karyawan.',
        ]);

        try {
            DB::beginTransaction();

            // Kunci course agar konsisten (mencegah delete/archive saat assign)
            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            // (Opsional) Batasi assign jika course dalam status tertentu
            // if ($fresh->status !== Course::STATUS_PUBLISHED) {
            //     DB::rollBack();
            //     return back()->with('error', 'Course belum dipublikasikan.');
            // }

            $now  = now();
            $uids = collect($data['user_ids'])->unique()->values();

            // Validasi defensif: pastikan user masih ada (belum soft-deleted) & belum enrolled
            $validUserIds = User::query()
                ->whereIn('id', $uids)
                ->pluck('id')
                ->all();

            if (empty($validUserIds)) {
                DB::rollBack();
                return back()->with('error', 'Tidak ada pengguna valid untuk di-assign.');
            }

            // Siapkan rows untuk upsert (unik: user_id + course_id)
            $rows = collect($validUserIds)->map(fn($uid) => [
                'user_id'     => $uid,
                'course_id'   => $fresh->id,
                'status'      => 'assigned',
                'enrolled_at' => $now,
                'created_at'  => $now,
                'updated_at'  => $now,
            ])->all();

            // Gunakan upsert agar idempotent (tidak error jika sudah ada)
            // Kolom unik: ['user_id','course_id'] — sesuaikan dengan index unik di tabel enrollments
            LmsEnrollment::upsert(
                $rows,
                ['user_id', 'course_id'],
                ['status', 'enrolled_at', 'updated_at']
            );

            // (Opsional) Ambil siapa saja yang BARU diassign untuk notifikasi
            $existingPairs = LmsEnrollment::query()
                ->where('course_id', $fresh->id)
                ->whereIn('user_id', $validUserIds)
                ->pluck('user_id')
                ->all();

            $newlyAssignedUserIds = array_values(array_diff($validUserIds, $existingPairs));

            DB::commit();

            // Kirim notifikasi/email setelah commit (agar tidak kirim jika DB gagal)
            // DB::afterCommit(function () use ($fresh, $newlyAssignedUserIds) {
            //     // Dispatch Job/Notification di sini
            //     // e.g., NotifyAssignedToCourse::dispatch($fresh->id, $newlyAssignedUserIds);
            // });

            return back()->with('success', 'Karyawan berhasil di-assign ke kursus.');
        } catch (Throwable $e) {
            DB::rollBack();

            // Log::error('Gagal assign course', ['course_id' => $course->id, 'error' => $e->getMessage()]);
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
