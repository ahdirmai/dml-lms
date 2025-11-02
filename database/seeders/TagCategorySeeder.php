<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lms\Category as LmsCategory;
use App\Models\Lms\Tag as LmsTag;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagCategorySeeder extends Seeder
{
    public function run(): void
    {
        LmsCategory::factory()->count(6)->create();
        LmsTag::factory()->count(12)->create();
    }
}
