<?php

namespace Database\Seeders;

use App\Models\Lms\Category;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Lesson;
use App\Models\Lms\Module;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizOption;
use App\Models\Lms\QuizQuestion;
use App\Models\Lms\Tag;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Instructor & Students
        $instructor = User::role('instructor')->first();
        if (! $instructor) {
            $instructor = User::factory()->create([
                'name' => 'Instructor Demo',
                'email' => 'instructor@demo.com',
            ]);
            $instructor->assignRole('instructor');
        }

        // Create some students if not enough
        $students = User::role('student')->get();
        if ($students->count() < 5) {
            User::factory(5)->create()->each(function ($u) {
                $u->assignRole('student');
            });
            $students = User::role('student')->get();
        }

        // 2. Setup Categories & Tags
        $categories = collect(['Pemrograman', 'Desain Grafis', 'Bisnis Digital', 'Marketing'])->map(function ($name) use ($instructor) {
            return Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => "Kategori tentang $name",
                    'created_by' => $instructor->id,
                ]
            );
        });

        $tags = collect(['laravel', 'php', 'react', 'vue', 'marketing', 'seo', 'design', 'figma'])->map(function ($tag) {
            return Tag::firstOrCreate(
                ['slug' => $tag],
                [
                    'id' => (string) Str::uuid(),
                    'name' => ucfirst($tag),
                ]
            );
        });

        // 3. Create 10 Courses per Instructor
        $instructors = User::role('instructor')->get();
        if ($instructors->isEmpty()) {
            $instructors = collect([$instructor]);
        }

        foreach ($instructors as $inst) {
            $this->command->info("Creating courses for instructor: {$inst->name}");
            for ($i = 1; $i <= 10; $i++) {
                $this->createCourse($i, $inst, $categories->random(), $tags->random(2), $students);
            }
        }

        // 4. Create Leaderboard Data
        $this->createLeaderboardData();

        $this->command->info('✅ LmsDemoSeeder selesai: 10 courses, modules, lessons, enrollments, & activity logs generated.');
    }

    private function createLeaderboardData()
    {
        $this->command->info('Generating Leaderboard Data...');

        // Create 10 specific users for leaderboard
        $leaderboardNames = [
            'Herman Kusuma',
            'Joko Widodo',
            'Susilo Bambang',
            'Megawati Putri',
            'Abdurrahman Wahid',
            'Bacharuddin Jusuf',
            'Soeharto Harto',
            'Soekarno Karno',
            'Mohammad Hatta',
            'Sudirman Dirman',
        ];

        $leaderboardUsers = collect();
        foreach ($leaderboardNames as $index => $name) {
            $i = $index + 1;
            $emailName = strtolower(str_replace(' ', '.', $name));

            $user = User::firstOrCreate(
                ['email' => "{$emailName}@lms.test"],
                [
                    'name' => $name,
                    'password' => bcrypt('password'), // or default
                    'email_verified_at' => now(),
                    'lms_status' => 'active',
                ]
            );
            $user->assignRole('student');

            // Create Profile
            \App\Models\UserProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'job_title' => collect(['Engineer', 'Manager', 'Analyst', 'Specialist'])->random(),
                    'department' => collect(['Operations', 'IT', 'HR', 'Finance'])->random(),
                ]
            );

            $leaderboardUsers->push($user);
        }

        $courses = Course::all();
        if ($courses->isEmpty()) {
            return;
        }

        foreach ($leaderboardUsers as $index => $user) {
            // Distribute completed courses count:
            // Top 1-3: 15-20 courses
            // Top 4-7: 10-14 courses
            // Top 8-10: 5-9 courses

            if ($index < 3) {
                $count = rand(15, 20);
            } elseif ($index < 7) {
                $count = rand(10, 14);
            } else {
                $count = rand(5, 9);
            }

            // Ensure we don't try to enroll in more courses than exist
            $count = min($count, $courses->count());

            $userCourses = $courses->random($count);

            foreach ($userCourses as $course) {
                $enrollment = Enrollment::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'status' => 'completed',
                        'enrolled_at' => now()->subDays(rand(30, 60)),
                        'completed_at' => now()->subDays(rand(1, 29)),
                    ]
                );

                // If course has post-test, create a high score attempt
                if ($course->posttest) {
                    // Score logic: Top users get higher scores
                    // Top 3: 95-100
                    // Others: 80-94
                    $score = ($index < 3) ? rand(95, 100) : rand(80, 94);

                    \App\Models\Lms\QuizAttempt::create([
                        'id' => (string) Str::uuid(),
                        'quiz_id' => $course->posttest->id,
                        'user_id' => $user->id,
                        'attempt_no' => 1,
                        'started_at' => now()->subHours(1),
                        'finished_at' => now(),
                        'score' => $score,
                        'passed' => true,
                        'duration_seconds' => rand(60, 900),
                    ]);
                }
            }
        }
    }

    private function createCourse($index, $instructor, $category, $tags, $students)
    {
        // Determine Variations
        // 1-3: Pretest & Posttest enabled
        // 4-6: Pretest & Posttest enabled + Require Pretest Pass
        // 7-8: No Pretest/Posttest
        // 9-10: Using Due Date (with Pre/Post)

        $hasPretest = true;
        $hasPosttest = true;
        $requirePretest = false;
        $usingDueDate = false;

        if ($index >= 4 && $index <= 6) {
            $requirePretest = true;
        } elseif ($index >= 7 && $index <= 8) {
            $hasPretest = false;
            $hasPosttest = false;
        } elseif ($index >= 9) {
            $usingDueDate = true;
        }

        $title = "Course Demo #$index: ".($hasPretest ? 'With Pretest' : 'No Pretest').($requirePretest ? ' (Required)' : '').($usingDueDate ? ' + Due Date' : '');

        $course = Course::create([
            'id' => (string) Str::uuid(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'subtitle' => 'Demo Course Subtitle for '.$index,
            'description' => "This is a demo course number $index. ".($usingDueDate ? 'This course has a due date.' : ''),
            'status' => 'published',
            'difficulty' => collect(['beginner', 'intermediate', 'advanced'])->random(),
            'instructor_id' => $instructor->id,
            'has_pretest' => $hasPretest,
            'has_posttest' => $hasPosttest,
            'default_passing_score' => 70,
            'pretest_passing_score' => 70,
            'posttest_passing_score' => 80,
            'require_pretest_before_content' => $requirePretest,
            'using_due_date' => $usingDueDate,
            'created_by' => $instructor->id,
            'thumbnail_path' => null, // Or a default image path
        ]);

        $course->categories()->sync([$category->id]);
        $course->tags()->sync($tags->pluck('id'));

        // Create Modules & Lessons
        for ($m = 1; $m <= 5; $m++) {
            $module = Module::create([
                'id' => (string) Str::uuid(),
                'course_id' => $course->id,
                'title' => "Module $m: Topic Discussion",
                'order' => $m,
            ]);

            // Lesson 1: Video
            Lesson::create([
                'id' => (string) Str::uuid(),
                'course_id' => $course->id,
                'module_id' => $module->id,
                'title' => "Lesson $m.1: Video Introduction",
                'description' => 'Video learning material.',
                'kind' => 'youtube',
                'youtube_video_id' => 'BX2FfdEnxxc',
                'content_url' => 'https://www.youtube.com/watch?v=BX2FfdEnxxc',
                'order_no' => 1,
                'duration_seconds' => 60,
            ]);

            // Lesson 2: GDrive
            Lesson::create([
                'id' => (string) Str::uuid(),
                'course_id' => $course->id,
                'module_id' => $module->id,
                'title' => "Lesson $m.2: Reading Material",
                'description' => 'PDF/Document reading material.',
                'kind' => 'gdrive',
                'gdrive_file_id' => '1yYutH31QF9DWzw_1YLAGrfRM4f4sYgWS',
                'content_url' => 'https://drive.google.com/file/d/1yYutH31QF9DWzw_1YLAGrfRM4f4sYgWS/view?usp=drive_link',
                'order_no' => 2,
                'duration_seconds' => 120,
            ]);
        }

        // Create Quizzes if enabled
        if ($hasPretest) {
            $this->createQuiz($course, 'pretest');
        }
        if ($hasPosttest) {
            $this->createQuiz($course, 'posttest');
        }

        // Assign Students & Create Activity
        $enrolledStudents = $students->random(rand(2, $students->count()));
        foreach ($enrolledStudents as $student) {
            $enrollment = Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active',
                'enrolled_at' => now()->subDays(rand(1, 30)),
            ]);

            // If course uses due date, set start and end date
            if ($usingDueDate) {
                \App\Models\Lms\EnrollmentDueDate::create([
                    'enrollment_id' => $enrollment->id,
                    'start_date' => now()->subDays(rand(1, 5)),
                    'end_date' => now()->addDays(rand(10, 30)),
                ]);
            }

            // Activity Log: Enrolled
            UserActivityLog::create([
                'user_id' => $student->id,
                'activity_type' => 'course_enrollment',
                'subject_type' => Course::class,
                'subject_id' => $course->id,
                'description' => "User enrolled in course {$course->title}",
                'ip_address' => '127.0.0.1',
            ]);

            // Randomly add some lesson activity
            if (rand(0, 1)) {
                UserActivityLog::create([
                    'user_id' => $student->id,
                    'activity_type' => 'lesson_view',
                    'subject_type' => Lesson::class,
                    'subject_id' => $course->lessons->first()->id,
                    'description' => "User viewed lesson {$course->lessons->first()->title}",
                    'ip_address' => '127.0.0.1',
                ]);
            }
        }
    }

    private function createQuiz($course, $kind)
    {
        $quiz = Quiz::create([
            'id' => (string) Str::uuid(),
            'title' => ucfirst($kind).' for '.$course->title,
            'quiz_kind' => $kind,
            'quizzable_type' => Course::class,
            'quizzable_id' => $course->id,
            'time_limit_seconds' => 600,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'passing_score' => $kind === 'pretest' ? $course->pretest_passing_score : $course->posttest_passing_score,
        ]);

        // Add Questions
        $questions = [
            ['What is Laravel?', ['PHP Framework' => true, 'JS Library' => false, 'OS' => false]],
            ['What is MVC?', ['Model View Controller' => true, 'Model View Component' => false, 'Many View Control' => false]],
            ['Command to create migration?', ['php artisan make:migration' => true, 'php artisan create:table' => false, 'make migration' => false]],
        ];

        foreach ($questions as $index => [$qText, $options]) {
            $q = QuizQuestion::create([
                'id' => (string) Str::uuid(),
                'quiz_id' => $quiz->id,
                'question_text' => $qText,
                'question_type' => 'mcq',
                'score' => 10,
                'order' => $index + 1,
            ]);

            foreach ($options as $optText => $isCorrect) {
                QuizOption::create([
                    'id' => (string) Str::uuid(),
                    'question_id' => $q->id,
                    'option_text' => $optText,
                    'is_correct' => $isCorrect,
                ]);
            }
        }
    }
}
