<?php

namespace Database\Factories\Lms;

use App\Models\Lms\Module;
use App\Models\Lms\Course;
use App\Models\Lms\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseModuleFactory extends Factory
{
    protected $model = CourseModule::class;

    public function definition(): array
    {
        return [
            'id'          => (string) Str::uuid(),
            'course_id'   => Course::factory(),
            'title'       => 'Module: ' . $this->faker->sentence(2),
            'description' => $this->faker->sentence(),
            'position'    => $this->faker->numberBetween(1, 10),
            'is_published' => true,
        ];
    }
}
