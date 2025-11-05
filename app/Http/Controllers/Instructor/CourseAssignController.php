<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Lms\Enrollment as LmsEnrollment;
use Illuminate\Http\Request;

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
                    $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
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
        ], ['user_ids.required' => 'Pilih minimal satu mahasiswa.']);

        // bulk upsert (unique: user_id, course_id)
        $now = now();
        $rows = collect($data['user_ids'])->map(fn($uid) => [
            'user_id' => $uid,
            'course_id' => $course->id,
            'status' => 'assigned',
            'enrolled_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        // gunakan insertOrIgnore agar tidak error jika ada yang sudah ada
        LmsEnrollment::insertOrIgnore($rows);

        return back()->with('success', 'Mahasiswa berhasil di-assign ke kursus.');
    }

    public function remove(Course $course, User $user)
    {
        LmsEnrollment::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Mahasiswa dihapus dari kursus.');
    }
}
