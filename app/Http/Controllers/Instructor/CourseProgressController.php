<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use Illuminate\Http\Request;
// use App\Models\Enrollment;
// use App\Models\LessonProgress;

class CourseProgressController extends Controller
{
    /**
     * Tampilkan halaman Progress Tracking sebuah course.
     * Saat ini pakai DUMMY DATA; query real data ada di komentar.
     */
    public function show(Request $request, Course $course)
    {
        // ============================
        // DUMMY DATA — tampilkan dulu
        // ============================
        $summary = [
            'students_total'     => 42,
            'students_active'    => 31,
            'students_completed' => 9,
            'avg_progress'       => 57, // %
        ];

        $moduleBreakdown = [
            ['id' => 1, 'title' => 'Introduction',     'lessons_total' => 5, 'lessons_completed' => 3],
            ['id' => 2, 'title' => 'Fundamentals',     'lessons_total' => 8, 'lessons_completed' => 4],
            ['id' => 3, 'title' => 'Advanced Topics',  'lessons_total' => 6, 'lessons_completed' => 2],
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

        // ==========================================================
        // REAL DATA — contoh query, tinggal aktifkan saat data siap
        // ==========================================================
        /**
        // 1) Ambil enrollments & user
        $enrollments = Enrollment::query()
            ->where('course_id', $course->id)
            ->with(['user:id,name,email'])
            ->get();

        // 2) Total lessons course (semua modul)
        $totalLessons = $course->lessons()->count(); // pastikan relasi Course->lessons ada

        // 3) Aggregate progress per enrollment
        $progressMap = LessonProgress::query()
            ->selectRaw('enrollment_id, SUM(CASE WHEN status="completed" THEN 1 ELSE 0 END) AS completed, COUNT(*) AS total')
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->groupBy('enrollment_id')
            ->get()
            ->keyBy('enrollment_id');

        // 4) Bentuk list students
        $students = $enrollments->map(function ($en) use ($progressMap, $totalLessons) {
            $agg = $progressMap[$en->id] ?? null;
            $completed = (int) ($agg->completed ?? 0);
            $total     = (int) ($agg->total ?: $totalLessons);
            $progress  = $total > 0 ? round(($completed / $total) * 100) : 0;
            // last activity: ambil max last_activity_at di lesson_progress enrollment tsb
            $lastActivity = optional(
                \App\Models\LessonProgress::where('enrollment_id', $en->id)->max('last_activity_at')
            )->format('Y-m-d H:i');

            return [
                'name'              => $en->user->name,
                'email'             => $en->user->email,
                'status'            => $en->status, // assigned|active|completed|cancelled
                'progress'          => $progress,
                'completed_lessons' => $completed,
                'total_lessons'     => $total,
                'last_activity'     => $lastActivity,
            ];
        })->values()->all();

        // 5) Summary
        $summary = [
            'students_total'     => $enrollments->count(),
            'students_active'    => $enrollments->where('status', 'active')->count(),
            'students_completed' => $enrollments->where('status', 'completed')->count(),
            'avg_progress'       => count($students) ? round(array_sum(array_column($students, 'progress')) / count($students)) : 0,
        ];

        // 6) Module breakdown
        $moduleBreakdown = $course->modules()
            ->withCount('lessons')
            ->get()
            ->map(function ($m) {
                // count completed lesson in this module across all students (opsional, atau pakai rata-rata per user)
                $completed = \App\Models\LessonProgress::whereIn(
                        'lesson_id',
                        $m->lessons()->pluck('id')
                    )->completed()->count();

                return [
                    'id' => $m->id,
                    'title' => $m->title,
                    'lessons_total' => $m->lessons_count ?? $m->lessons_count ?? $m->lessons()->count(),
                    // untuk contoh sederhana, kita isi jumlah completed relatif
                    'lessons_completed' => min($completed, $m->lessons()->count()),
                ];
            })->toArray();
         **/

        return view('admin.pages.courses.progress', compact(
            'course',
            'summary',
            'moduleBreakdown',
            'students'
        ));
    }
}
