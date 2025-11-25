<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lms\Enrollment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

use App\Services\UserCourseService;

class DashboardController extends Controller
{
    protected $userCourseService;

    public function __construct(UserCourseService $userCourseService)
    {
        $this->userCourseService = $userCourseService;
    }
    public function index()
    {
        // 1. User info
        $user = Auth::user();
        $userInfo = [
            'name' => $user->name,
            'position' => $user->position ?? 'Karyawan',
            'vessel' => $user->vessel ?? 'N/A',
            'rankId' => $user->rankId ?? null,
        ];

        // 2. Ambil data enrollments nyata
        $enrollments = $this->userCourseService->getUserEnrollments($user->id);

        // 3. Format jadi array untuk view (TERMASUK URL PRE/POST/REVIEW)
        $coursesArray = $this->userCourseService->formatEnrollments($enrollments);

        // 4. Dummy leaderboard
        $leaderboardData = $this->getDummyLeaderboard();

        // 5. Statistik performa
        $performance = $this->userCourseService->calculatePerformance($coursesArray);

        // return $enrollments;

        return view('user.dashboard.index', [
            'userInfo' => $userInfo,
            'courses' => $coursesArray,
            'leaderboardData' => $leaderboardData,
            'performance' => $performance,
        ]);
    }



    private function getDummyLeaderboard(): array
    {
        return [
            'postTest' => [
                ['name' => 'Siti', 'score' => 98, 'category' => 'HSSE', 'isYou' => false, 'icon' => 'user'],
                ['name' => 'Agus', 'score' => 95, 'category' => 'Operation', 'isYou' => false, 'icon' => 'user'],
                ['name' => 'Dewi', 'score' => 92, 'category' => 'Finance', 'isYou' => false, 'icon' => 'user'],
                ['name' => 'Budi Santoso', 'score' => 88, 'isYou' => true,  'rank' => 4, 'icon' => 'user'],
                ['name' => 'Joko Widodo', 'score' => 85, 'isYou' => false, 'rank' => 5, 'icon' => 'user'],
                ['name' => 'Rina Wijaya', 'score' => 80, 'isYou' => false, 'rank' => 6, 'icon' => 'user'],
                ['name' => 'Herman Kusuma', 'score' => 75, 'isYou' => false, 'rank' => 7, 'icon' => 'user'],
            ],
            'completedCourses' => [
                ['name' => 'Siti',          'count' => 8, 'category' => 'HSSE',     'isYou' => false, 'icon' => 'user'],
                ['name' => 'Joko',          'count' => 7, 'category' => 'IT',       'isYou' => false, 'icon' => 'user'],
                ['name' => 'Agus',          'count' => 6, 'category' => 'Operation', 'isYou' => false, 'icon' => 'user'],
                ['name' => 'Dewi Lestari',  'count' => 5, 'isYou' => false, 'rank' => 4, 'icon' => 'user'],
                ['name' => 'Herman Kusuma', 'count' => 5, 'isYou' => false, 'rank' => 5, 'icon' => 'user'],
                ['name' => 'Budi Santoso',  'count' => 4, 'isYou' => true,  'rank' => 6, 'icon' => 'user'],
                ['name' => 'Rina Wijaya',   'count' => 3, 'isYou' => false, 'rank' => 7, 'icon' => 'user'],
            ],
        ];
    }
}
