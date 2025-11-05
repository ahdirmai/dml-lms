<?php

namespace Database\Factories\Lms;

use App\Models\Category;
use App\Models\Lms\Category as LmsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = LmsCategory::class;
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'id' => (string) Str::uuid(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
        ];
    }
}
