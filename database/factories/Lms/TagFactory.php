<?php

namespace Database\Factories\Lms;

use App\Models\Lms\Tag as LmsTag;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = LmsTag::class;
    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        return [
            'id' => (string) Str::uuid(),
            'name' => strtolower($name),
            'slug' => Str::slug($name),
        ];
    }
}
