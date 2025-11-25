<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\User;
use App\Models\Lms\Enrollment as LmsEnrollment;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class CourseAssignController extends Controller
{
    public function form(Request $request, Course $course)
    {
        // Hanya pemilik course yang boleh akses
        abort_unless($course->instructor_id === Auth::id(), 403);

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

        return view('instructor.pages.courses.assign-students', compact('course', 'available', 'enrolled', 'q'));
    }

    public function store(Request $request, Course $course)
    {
        // Hanya pemilik course yang boleh assign
        abort_unless($course->instructor_id === Auth::id(), 403);

        $data = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ], [
            'user_ids.required' => 'Pilih minimal satu mahasiswa.',
        ]);

        try {
            DB::beginTransaction();

            // Kunci course agar konsisten selama proses assign
            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            $now  = now();
            $uids = collect($data['user_ids'])->unique()->values();

            // Siapkan rows untuk upsert (unik: user_id + course_id)
            $rows = $uids->map(fn($uid) => [
                'user_id'     => $uid,
                'course_id'   => $fresh->id,
                'status'      => 'assigned',
                'enrolled_at' => $now,
                'created_at'  => $now,
                'updated_at'  => $now,
            ])->all();

            // Idempotent: upsert berdasarkan (user_id, course_id)
            LmsEnrollment::upsert(
                $rows,
                ['user_id', 'course_id'],
                ['status', 'enrolled_at', 'updated_at']
            );

            DB::commit();

            // (Opsional) kirim notifikasi setelah commit
            // DB::afterCommit(fn () => NotifyAssignedStudents::dispatch($fresh->id, $uids->all()));

            // Log activity
            UserActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'assign_students',
                'subject_type' => Course::class,
                'subject_id' => $fresh->id,
                'description' => "Assigned " . count($uids) . " students to course: {$fresh->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Mahasiswa berhasil di-assign ke kursus.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat assign: ' . $e->getMessage());
        }
    }

    public function remove(Course $course, User $user)
    {
        // Hanya pemilik course yang boleh menghapus enrollment
        abort_unless($course->instructor_id === Auth::id(), 403);

        try {
            DB::beginTransaction();

            // Kunci course agar konsisten
            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            $deleted = LmsEnrollment::query()
                ->where('course_id', $fresh->id)
                ->where('user_id', $user->id)
                ->delete();

            DB::commit();

            if ($deleted) {
                // DB::afterCommit(fn () => NotifyUnassignedStudent::dispatch($fresh->id, $user->id));
                
                // Log activity
                UserActivityLog::create([
                    'user_id' => Auth::id(),
                    'activity_type' => 'remove_student',
                    'subject_type' => Course::class,
                    'subject_id' => $fresh->id,
                    'description' => "Removed student {$user->name} from course: {$fresh->title}",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                
                return back()->with('success', 'Mahasiswa dihapus dari kursus.');
            }

            return back()->with('info', 'Mahasiswa tidak terdaftar pada kursus ini.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat menghapus mahasiswa dari kursus: ' . $e->getMessage());
        }
    }
}
