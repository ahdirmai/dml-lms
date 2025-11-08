<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Lms\Category;
use App\Models\Lms\Tag;
use App\Models\Lms\Course;
use App\Models\Lms\Module;
use App\Models\Lms\Lesson;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Models\Lms\QuizOption;

class LmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil user pertama sebagai instruktur, buat jika belum ada
        $instructor = User::query()->first() ?? User::factory()->create();

        // ===== CATEGORIES =====
        $category = Category::firstOrCreate(
            ['slug' => 'pemrograman'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Pemrograman',
                'description' => 'Belajar konsep dan praktik coding.',
                'created_by' => $instructor->id ?? null,
            ]
        );

        // ===== TAGS =====
        $tags = collect(['laravel', 'php', 'backend'])->map(function ($tag) {
            return Tag::firstOrCreate(
                ['slug' => $tag],
                [
                    'id' => (string) Str::uuid(),
                    'name' => ucfirst($tag),
                ]
            );
        });

        // ===== COURSE =====
        $course = Course::firstOrCreate(
            ['slug' => 'laravel-dasar'],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Laravel Dasar',
                'subtitle' => 'Fundamental Framework PHP Modern',
                'description' => 'Pelajari routing, controller, model, dan migration di Laravel.',
                'status' => 'published',
                'difficulty' => 'beginner',
                'instructor_id' => $instructor->id,
                'has_pretest' => true,
                'has_posttest' => true,
                'default_passing_score' => 70,
                'pretest_passing_score' => 0,
                'posttest_passing_score' => 80,
                'require_pretest_before_content' => true,
            ]
        );

        // Attach kategori & tag
        $course->categories()->syncWithoutDetaching([$category->id]);
        $course->tags()->syncWithoutDetaching($tags->pluck('id')->toArray());

        // ===== MODULE =====
        $module = Module::firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Pengenalan Laravel'],
            [
                'id' => (string) Str::uuid(),
                'order' => 1,
            ]
        );

        // ===== LESSONS =====
        $lesson1 = Lesson::firstOrCreate(
            ['slug' => 'intro-laravel'],
            [
                'id' => (string) Str::uuid(),
                'course_id' => $course->id,
                'module_id' => $module->id,
                'title' => 'Apa itu Laravel?',
                'description' => 'Menjelaskan dasar-dasar framework Laravel dan sejarahnya.',
                'kind' => 'youtube',
                'youtube_video_id' => 'dQw4w9WgXcQ',
                'order_no' => 1,
            ]
        );

        $lesson2 = Lesson::firstOrCreate(
            ['slug' => 'routing-laravel'],
            [
                'id' => (string) Str::uuid(),
                'course_id' => $course->id,
                'module_id' => $module->id,
                'title' => 'Routing di Laravel',
                'description' => 'Mengenal sistem routing Laravel dan penggunaannya.',
                'kind' => 'quiz',
                'order_no' => 2,
            ]
        );

        // ===== QUIZZES =====
        // Pretest (polymorphic ke Course)
        $pretest = $course->pretest()->first() ?? Quiz::create([
            'id' => (string) Str::uuid(),
            'title' => 'Pretest Laravel Dasar',
            'quiz_kind' => 'pretest',
            'quizzable_type' => Course::class,
            'quizzable_id' => $course->id,
            'time_limit_seconds' => 300,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'passing_score' => null, // gunakan pretest_passing_score = 0
        ]);

        // Posttest (polymorphic ke Course)
        $posttest = $course->posttest()->first() ?? Quiz::create([
            'id' => (string) Str::uuid(),
            'title' => 'Posttest Laravel Dasar',
            'quiz_kind' => 'posttest',
            'quizzable_type' => Course::class,
            'quizzable_id' => $course->id,
            'time_limit_seconds' => 600,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'passing_score' => 80,
        ]);

        // Quiz untuk lesson (polymorphic ke Lesson)
        $lessonQuiz = $lesson2->quiz()->first() ?? Quiz::create([
            'id' => (string) Str::uuid(),
            'title' => 'Quiz: Routing Dasar',
            'quiz_kind' => 'regular',
            'quizzable_type' => Lesson::class,
            'quizzable_id' => $lesson2->id,
            'time_limit_seconds' => 300,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'passing_score' => 70,
        ]);

        // ===== QUESTIONS & OPTIONS =====
        $this->seedQuestions($pretest, [
            ['Apa kepanjangan MVC?', [
                ['Model View Controller', true],
                ['Module View Controller', false],
                ['Model Value Chain', false],
            ]],
            ['Perintah artisan untuk membuat model?', [
                ['php artisan make:model', true],
                ['composer create:model', false],
                ['php make:model', false],
            ]],
        ]);

        $this->seedQuestions($posttest, [
            ['Middleware digunakan untuk?', [
                ['Memfilter request sebelum sampai controller', true],
                ['Mengelola tampilan blade', false],
                ['Menjalankan migration', false],
            ]],
            ['Perintah artisan untuk membuat controller?', [
                ['php artisan make:controller', true],
                ['composer make:controller', false],
                ['php artisan new:controller', false],
            ]],
        ]);

        $this->seedQuestions($lessonQuiz, [
            ['Route::get("/user") berfungsi untuk?', [
                ['Menangani request GET ke /user', true],
                ['Menangani POST /user', false],
                ['Menjalankan seeder', false],
            ]],
        ]);

        $this->command->info('✅ LmsDemoSeeder selesai: 1 course + 1 module + 2 lesson + pretest/posttest/quiz terbuat.');
    }

    private function seedQuestions(Quiz $quiz, array $data): void
    {
        foreach ($data as [$text, $options]) {
            $question = QuizQuestion::create([
                'id' => (string) Str::uuid(),
                'quiz_id' => $quiz->id,
                'question' => $text,
                'qtype' => 'mcq',
                'score' => 1,
                'order' => 1,
            ]);

            $order = 1;
            foreach ($options as [$opt, $isCorrect]) {
                QuizOption::create([
                    'id' => (string) Str::uuid(),
                    'question_id' => $question->id,
                    'option_text' => $opt,
                    'is_correct' => $isCorrect,
                ]);
                $order++;
            }
        }
    }
}
