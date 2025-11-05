<?php

namespace Database\Factories\Lms;

use App\Models\Lms\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = ucfirst($this->faker->unique()->sentence(3));
        return [
            'id'               => (string) Str::uuid(),
            'title'            => $title,
            'slug'             => Str::slug($title) . '-' . Str::random(5),
            'subtitle'         => $this->faker->sentence(),
            'description'      => $this->faker->paragraphs(2, true),
            'thumbnail_url'    => $this->faker->imageUrl(800, 450, 'education', true),
            'language'         => 'en',
            'level'            => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'visibility'       => 'public',
            'status'           => 'draft',
            'published_at'     => null,
            'created_by'       => \App\Models\User::factory(),
            'duration_minutes' => 0,
            'lessons_count'    => 0,
        ];
    }

    public function published(): self
    {
        return $this->state(fn() => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
