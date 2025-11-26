<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lms\Enrollment;
use App\Models\Lms\QuizAttempt;
use App\Services\UserCourseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // 4. Real leaderboard
        $leaderboardData = $this->getLeaderboardData($user->id);

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

    private function getLeaderboardData(string $currentUserId): array
    {
        // --- 1. Top Completed Courses ---
        // Ambil user dengan jumlah course completed terbanyak
        $topCompleted = Enrollment::select('user_id', DB::raw('count(*) as count'))
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->take(7)
            ->with(['user.profile'])
            ->get();

        // Format data
        $completedCourses = $topCompleted->map(function ($item, $index) use ($currentUserId) {
            $user = $item->user;
            $isYou = $user->id === $currentUserId;
            $category = $user->profile->job_title ?? $user->profile->department ?? 'N/A';

            return [
                'name' => $user->name,
                'count' => $item->count,
                'category' => $category,
                'isYou' => $isYou,
                'rank' => $index + 1,
                'icon' => 'user',
            ];
        })->toArray();

        // Cek apakah current user ada di top 7
        $currentUserInTopCompleted = collect($completedCourses)->contains('isYou', true);

        // Jika tidak ada, cari rank user ini
        if (! $currentUserInTopCompleted) {
            $myCount = Enrollment::where('user_id', $currentUserId)
                ->where('status', 'completed')
                ->count();

            // Hitung rank: jumlah user yang punya completed course LEBIH BANYAK dari user ini
            $rank = Enrollment::select('user_id', DB::raw('count(*) as count'))
                ->where('status', 'completed')
                ->groupBy('user_id')
                ->having('count', '>', $myCount)
                ->get()
                ->count() + 1;

            $user = Auth::user();
            $category = $user->profile->job_title ?? $user->profile->department ?? 'N/A';

            $completedCourses[] = [
                'name' => $user->name,
                'count' => $myCount,
                'category' => $category,
                'isYou' => true,
                'rank' => $rank,
                'icon' => 'user',
            ];
        }

        // --- 2. Top Post Test Scores ---
        // Ambil user dengan nilai post-test TERTINGGI (Max Score)
        // Asumsi: Kita ambil nilai tertinggi dari SALAH SATU post-test yang pernah dikerjakan user
        // Atau: Rata-rata? Biasanya leaderboard itu "Highest Score" dari single attempt atau "Total Score"?
        // Sesuai dummy: 'score' => 98. Ini terlihat seperti single score (skala 0-100).
        // Mari kita ambil MAX score yang pernah didapat user di post-test APAPUN.

        $topScores = QuizAttempt::whereHas('quiz', function ($q) {
            $q->where('quiz_kind', 'posttest');
        })
            ->select('user_id', DB::raw('MAX(score) as max_score'))
            ->groupBy('user_id')
            ->orderByDesc('max_score')
            ->take(7)
            ->with(['user.profile'])
            ->get();

        $postTestScores = $topScores->map(function ($item, $index) use ($currentUserId) {
            $user = $item->user;
            $isYou = $user->id === $currentUserId;
            $category = $user->profile->job_title ?? $user->profile->department ?? 'N/A';

            return [
                'name' => $user->name,
                'score' => (int) $item->max_score,
                'category' => $category,
                'isYou' => $isYou,
                'rank' => $index + 1,
                'icon' => 'user',
            ];
        })->toArray();

        // Cek current user
        $currentUserInTopScores = collect($postTestScores)->contains('isYou', true);

        if (! $currentUserInTopScores) {
            $myMaxScore = QuizAttempt::where('user_id', $currentUserId)
                ->whereHas('quiz', fn ($q) => $q->where('quiz_kind', 'posttest'))
                ->max('score');

            $myScore = $myMaxScore !== null ? (int) $myMaxScore : 0;

            // Hitung rank
            $rank = QuizAttempt::whereHas('quiz', fn ($q) => $q->where('quiz_kind', 'posttest'))
                ->select('user_id', DB::raw('MAX(score) as max_score'))
                ->groupBy('user_id')
                ->having('max_score', '>', $myScore)
                ->get()
                ->count() + 1;

            $user = Auth::user();
            $category = $user->profile->job_title ?? $user->profile->department ?? 'N/A';

            $postTestScores[] = [
                'name' => $user->name,
                'score' => $myScore,
                'category' => $category,
                'isYou' => true,
                'rank' => $rank,
                'icon' => 'user',
            ];
        }

        return [
            'completedCourses' => $completedCourses,
            'postTest' => $postTestScores,
        ];
    }
}
