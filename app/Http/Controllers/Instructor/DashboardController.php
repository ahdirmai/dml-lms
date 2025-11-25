<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $instructorId = Auth::id();

        // 1. Stats Cards
        $totalCourses = Course::where('instructor_id', $instructorId)->count();
        $publishedCourses = Course::where('instructor_id', $instructorId)->where('status', 'published')->count();
        
        // Total students (unique users enrolled in instructor's courses)
        $totalStudents = Enrollment::whereHas('course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })->distinct('user_id')->count('user_id');

        // Total completions
        $totalCompletions = Enrollment::whereHas('course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })->where('status', 'completed')->count();

        // 2. Recent Enrollments (limit 5)
        $recentEnrollments = Enrollment::with(['user', 'course'])
            ->whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
            ->latest()
            ->take(5)
            ->get();

        // 3. Top Courses by Student Count (limit 5)
        $topCourses = Course::where('instructor_id', $instructorId)
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get();

        return view('instructor.dashboard', compact(
            'totalCourses',
            'publishedCourses',
            'totalStudents',
            'totalCompletions',
            'recentEnrollments',
            'topCourses'
        ));
    }
}
