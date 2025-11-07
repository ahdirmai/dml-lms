<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Dummy User Info
        $userInfo = [
            'name' => "Bagas Satria",
            'position' => "Mekanik",
            'vessel' => "Kapal TSL 02",
            'rankId' => 105,
        ];

        // 2. Dummy Courses Data
        $courses = $this->getDummyCourses();

        // 3. Dummy Leaderboard Data
        $leaderboardData = $this->getDummyLeaderboard();

        // 4. Calculate Performance Stats (sebelumnya di JS, sekarang di backend)
        $performance = $this->calculatePerformance($courses);

        return view('user.dashboard.index', [
            'userInfo' => $userInfo,
            'courses' => $courses,
            'leaderboardData' => $leaderboardData,
            'performance' => $performance,
        ]);
    }
    /**
     * Helper untuk menghitung statistik performa.
     */
    private function calculatePerformance(array $courses): array
    {
        $stats = [
            'total' => 0,
            'completed' => 0,
            'inProgress' => 0,
            'notStarted' => 0,
            'expired' => 0,
            'totalModules' => 0,
            'completedModules' => 0,
        ];

        foreach ($courses as $course) {
            $stats['total']++;
            $stats['totalModules'] += $course['totalModules'];

            switch ($course['status']) {
                case 'Completed':
                    $stats['completed']++;
                    break;
                case 'In Progress':
                    $stats['inProgress']++;
                    break;
                case 'Not Started':
                    $stats['notStarted']++;
                    break;
                case 'Expired':
                    $stats['expired']++;
                    break;
            }

            foreach ($course['modules'] as $module) {
                if ($module['status'] === 'completed') {
                    $stats['completedModules']++;
                }
            }
        }

        // Calculate Overall Progress
        $stats['overallProgress'] = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;

        // Mock Compliance Goal
        $complianceGoal = 3;
        $stats['complianceGoal'] = $complianceGoal;
        $stats['complianceProgress'] = min(100, round(($stats['completed'] / $complianceGoal) * 100));


        return $stats;
    }

    /**
     * Helper untuk data leaderboard.
     */
    private function getDummyLeaderboard(): array
    {
        return [
            'postTest' => [
                ['name' => "Siti", 'score' => 98, 'category' => "HSSE", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Agus", 'score' => 95, 'category' => "Operation", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Dewi", 'score' => 92, 'category' => "Finance", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Budi Santoso", 'score' => 88, 'isYou' => true, 'rank' => 4, 'icon' => 'user'],
                ['name' => "Joko Widodo", 'score' => 85, 'isYou' => false, 'rank' => 5, 'icon' => 'user'],
                ['name' => "Rina Wijaya", 'score' => 80, 'isYou' => false, 'rank' => 6, 'icon' => 'user'],
                ['name' => "Herman Kusuma", 'score' => 75, 'isYou' => false, 'rank' => 7, 'icon' => 'user'],
            ],
            'completedCourses' => [
                ['name' => "Siti", 'count' => 8, 'category' => "HSSE", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Joko", 'count' => 7, 'category' => "IT", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Agus", 'count' => 6, 'category' => "Operation", 'isYou' => false, 'icon' => 'user'],
                ['name' => "Dewi Lestari", 'count' => 5, 'isYou' => false, 'rank' => 4, 'icon' => 'user'],
                ['name' => "Herman Kusuma", 'count' => 5, 'isYou' => false, 'rank' => 5, 'icon' => 'user'],
                ['name' => "Budi Santoso", 'count' => 4, 'isYou' => true, 'rank' => 6, 'icon' => 'user'],
                ['name' => "Rina Wijaya", 'count' => 3, 'isYou' => false, 'rank' => 7, 'icon' => 'user'],
            ]
        ];
    }

    /**
     * Helper untuk data kursus.
     */
    private function getDummyCourses(): array
    {
        return [
            [
                'id' => 'course-1',
                'title' => "Safety Management & Vessel Operations",
                'subtitle' => "Sistem Manajemen Keselamatan Kapal dan Operasi",
                'category' => "HSSE",
                'assignedOn' => "12 Sep 2025",
                'assignedBy' => "HSSE Dept.",
                'totalModules' => 10,
                'totalDuration' => 45,
                'preTestScore' => null,
                'postTestScore' => null,
                'progress' => 0,
                'lastActivity' => "-",
                'status' => "Not Started",
                'modules' => [
                    ['no' => 1, 'title' => "Introduction to Safety Management", 'duration' => 4, 'status' => 'locked'],
                    ['no' => 2, 'title' => "ISM Code Overview", 'duration' => 5, 'status' => 'locked'],
                    ['no' => 3, 'title' => "Hazard Identification & Risk Assessment", 'duration' => 6, 'status' => 'locked'],
                    ['no' => 4, 'title' => "Permit to Work System", 'duration' => 4, 'status' => 'locked'],
                    ['no' => 5, 'title' => "PPE (Personal Protective Equipment)", 'duration' => 5, 'status' => 'locked'],
                    ['no' => 6, 'title' => "Safe Working Practices", 'duration' => 5, 'status' => 'locked'],
                    ['no' => 7, 'title' => "Emergency Procedures", 'duration' => 5, 'status' => 'locked'],
                    ['no' => 8, 'title' => "Reporting & Documentation", 'duration' => 3, 'status' => 'locked'],
                    ['no' => 9, 'title' => "Safety Meetings & Toolbox Talks", 'duration' => 4, 'status' => 'locked'],
                    ['no' => 10, 'title' => "Continuous Improvement & Audits", 'duration' => 4, 'status' => 'locked'],
                ],
                'description' => "This training provides a foundational understanding of Safety Management Systems (SMS) for both vessel crew and onshore operations teams.",
                'learningObjectives' => [
                    "Understand the purpose and structure of the SMS manual",
                    "Implement safe work procedures on board",
                    "Identify emergency response responsibilities",
                    "Recognize unsafe acts and report near-misses",
                    "Apply continuous improvement to safety culture",
                ],
                'preTest' => [
                    ['q' => "Apa tujuan utama SMS (Safety Management System)?", 'options' => ["Meningkatkan penjualan", "Mencegah kecelakaan & polusi", "Mengurangi jumlah kru", "Meningkatkan konsumsi bahan bakar"], 'answer' => 1],
                    ['q' => "Salah satu komponen ISM adalah?", 'options' => ["Permit to Work", "Invoice Payment", "Marketing Plan", "None"], 'answer' => 0],
                    ['q' => "Yang bukan PPE adalah?", 'options' => ["Helmet", "Gloves", "Shoes", "Sunglasses sebagai fashion"], 'answer' => 3]
                ],
                'postTest' => [
                    ['q' => "Laporan near-miss sebaiknya dilakukan pada?", 'options' => ["Saat libur", "Tidak perlu", "Segera setelah kejadian", "Setahun sekali"], 'answer' => 2],
                    ['q' => "Audit internal bertujuan untuk?", 'options' => ["Menjatuhkan pegawai", "Memperbaiki proses & kepatuhan", "Hanya formalitas", "Tidak penting"], 'answer' => 1],
                    ['q' => "Continuous improvement berarti?", 'options' => ["Perbaikan terus menerus", "Henti setelah satu kali", "Hanya ide manajemen", "Tidak ada"], 'answer' => 0]
                ]
            ],
            [
                'id' => 'course-2',
                'title' => "Emergency Procedures & Crew Response",
                'subtitle' => "Prosedur Darurat dan Respon Awak Kapal",
                'category' => "Operations",
                'assignedOn' => "20 Sep 2025",
                'assignedBy' => "Operations Training Dept",
                'totalModules' => 8,
                'totalDuration' => 35,
                'preTestScore' => 70,
                'postTestScore' => null,
                'progress' => 40,
                'lastActivity' => "25 Sep 2025",
                'status' => "In Progress",
                'modules' => [
                    ['no' => 1, 'title' => "Introduction to Emergency Response", 'duration' => 4, 'status' => 'completed'],
                    ['no' => 2, 'title' => "Alarm & Muster Procedures", 'duration' => 5, 'status' => 'completed'],
                    ['no' => 3, 'title' => "Fire Drill", 'duration' => 6, 'status' => 'completed'],
                    ['no' => 4, 'title' => "Abandon Ship Drill", 'duration' => 5, 'status' => 'in-progress'],
                    ['no' => 5, 'title' => "Man Overboard", 'duration' => 5, 'status' => 'locked'],
                    ['no' => 6, 'title' => "Oil Spill Management", 'duration' => 5, 'status' => 'locked'],
                    ['no' => 7, 'title' => "Emergency Communication", 'duration' => 3, 'status' => 'locked'],
                    ['no' => 8, 'title' => "Post-Emergency Review", 'duration' => 2, 'status' => 'locked'],
                ],
                'description' => "Pelatihan ini mencakup langkah-langkah kritis yang harus diikuti oleh semua kru dalam situasi darurat di kapal.",
                'learningObjectives' => ["Identify emergency types", "Know each crew's role in drills", "Follow ISM emergency protocol", "Practice communication chain"],
                'preTest' => [
                    ['q' => "Langkah pertama saat alarm dibunyikan adalah?", 'options' => ["Lari keluar", "Pergi ke muster station", "Matikan alarm", "Tidak melakukan apa-apa"], 'answer' => 1],
                    ['q' => "Abandon ship drill mensimulasikan?", 'options' => ["Kecelakaan kerja", "Evakuasi kapal", "Perbaikan mesin", "Check-in penumpang"], 'answer' => 1],
                    ['q' => "Who must follow emergency chain?", 'options' => ["Only captain", "Only crew", "All crew & passengers", "Only engineers"], 'answer' => 2]
                ],
                'postTest' => [
                    ['q' => "Komunikasi darurat harus dilakukan via?", 'options' => ["RUMOR", "Chain of command", "Media sosial", "Tidak perlu"], 'answer' => 1],
                    ['q' => "Abandon ship drill dilaksanakan kapan?", 'options' => ["Secara rutin", "Sekali seumur hidup", "Saat kapal kosong", "Never"], 'answer' => 0],
                    ['q' => "Fire drill tujuannya?", 'options' => ["Melatih respons kebakaran", "Dekorasi kapal", "Meningkatkan konsumsi bahan bakar", "Hiburan"], 'answer' => 0]
                ]
            ],
            // Anda bisa menambahkan course-3 dan course-4 dari file HTML di sini jika perlu
            // Untuk demo, 2 kursus sudah cukup
        ];
    }
}
