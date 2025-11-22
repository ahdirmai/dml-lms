<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\Category;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Statistics
        $totalUsers = User::count();
        $totalCourses = Course::count();
        $totalEnrollments = Enrollment::count();
        $totalCategories = Category::count();

        // 2. Course Statistics
        $publishedCourses = Course::where('status', 'published')->count();
        $draftCourses = Course::where('status', 'draft')->count();

        // 3. Enrollment Statistics
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        $pendingEnrollments = Enrollment::where('status', 'pending')->count();

        // 4. Recent Enrollments (last 7 days)
        $recentEnrollments = Enrollment::where('created_at', '>=', now()->subDays(7))->count();

        // 5. User Role Distribution
        $studentCount = User::role('student')->count();
        $instructorCount = User::role('instructor')->count();
        $adminCount = User::role('admin')->count();

        // 6. Top 5 Most Enrolled Courses
        $topCourses = Course::withCount('enrollments')
            ->with('instructor')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();

        // 7. Recent Activities (Latest 10 enrollments with user and course info)
        $recentActivities = Enrollment::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 8. Course Completion Rate
        $totalActiveEnrollments = Enrollment::whereIn('status', ['active', 'completed'])->count();
        $completionRate = $totalActiveEnrollments > 0
            ? round(($completedEnrollments / $totalActiveEnrollments) * 100, 2)
            : 0;

        // 9. Monthly Enrollment Trend (last 6 months)
        $monthlyEnrollments = Enrollment::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // 10. Category Distribution
        $categoryDistribution = Category::withCount('courses')
            ->having('courses_count', '>', 0)
            ->orderBy('courses_count', 'desc')
            ->limit(10)
            ->get();

        // 11. Recent New Users (last 10)
        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 12. User Growth (last 6 months)
        $userGrowth = User::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // 13. Average Progress per Course
        // Calculate based on lesson progress
        $enrollmentsWithProgress = Enrollment::whereIn('status', ['active', 'completed'])
            ->with(['course.lessons', 'lessonProgress'])
            ->get();
        
        $totalProgress = 0;
        $countEnrollments = 0;
        
        foreach ($enrollmentsWithProgress as $enrollment) {
            $totalLessons = $enrollment->course->lessons->count();
            if ($totalLessons > 0) {
                $completedLessons = $enrollment->lessonProgress()
                    ->where('status', 'completed')
                    ->count();
                $progress = ($completedLessons / $totalLessons) * 100;
                $totalProgress += $progress;
                $countEnrollments++;
            }
        }
        
        $avgProgress = $countEnrollments > 0 ? round($totalProgress / $countEnrollments, 1) : 0;

        // 14. This Week Statistics
        $thisWeekEnrollments = Enrollment::where('created_at', '>=', now()->startOfWeek())->count();
        $thisWeekUsers = User::where('created_at', '>=', now()->startOfWeek())->count();
        $thisWeekCompletions = Enrollment::where('status', 'completed')
            ->where('updated_at', '>=', now()->startOfWeek())
            ->count();

        return view('admin.pages.dashboard.index', compact(
            'totalUsers',
            'totalCourses',
            'totalEnrollments',
            'totalCategories',
            'publishedCourses',
            'draftCourses',
            'activeEnrollments',
            'completedEnrollments',
            'pendingEnrollments',
            'recentEnrollments',
            'studentCount',
            'instructorCount',
            'adminCount',
            'topCourses',
            'recentActivities',
            'completionRate',
            'monthlyEnrollments',
            'categoryDistribution',
            'recentUsers',
            'userGrowth',
            'avgProgress',
            'thisWeekEnrollments',
            'thisWeekUsers',
            'thisWeekCompletions'
        ));
    }
}
