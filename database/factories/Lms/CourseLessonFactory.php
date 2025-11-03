<?php

namespace Database\Factories\Lms;

use App\Models\Lms\CourseLesson;
use App\Models\Lms\CourseModule;
use App\Models\Lms\Lesson;
use App\Models\Lms\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseLessonFactory extends Factory
{
    protected $model = CourseLesson::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['text', 'video']);
        return [
            'id'               => (string) Str::uuid(),
            'module_id'        => CourseModule::factory(),
            'title'            => 'Lesson: ' . $this->faker->sentence(3),
            'content_type'     => $type,
            'body'             => $type === 'text' ? $this->faker->paragraphs(2, true) : null,
            'video_url'        => $type === 'video' ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
            'meta'             => null,
            'duration_minutes' => $this->faker->numberBetween(3, 15),
            'position'         => $this->faker->numberBetween(1, 20),
            'is_preview'       => $this->faker->boolean(15),
            'is_published'     => true,
        ];
    }
}
