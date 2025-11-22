<?php

namespace Database\Seeders;

use App\Models\Lms\Course;
use App\Models\Lms\CourseLesson;
use App\Models\Lms\CourseModule;
use App\Models\Lms\Module;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseModuleLessonSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::role('instructor')->first()
            ?? User::role('admin')->first()
            ?? User::first();

        Course::factory()
            ->count(3)
            ->state(fn () => ['created_by' => $author?->id])
            ->create()
            ->each(function (Course $course) {
                // Buat 3 module dengan sequence agar position berurutan & course_id benar
                $modules = CourseModule::factory()
                    ->count(3)
                    ->sequence(
                        ['course_id' => $course->id, 'position' => 1],
                        ['course_id' => $course->id, 'position' => 2],
                        ['course_id' => $course->id, 'position' => 3],
                    )
                    ->create();

                $totalDuration = 0;
                $totalLessons = 0;

                foreach ($modules as $module) {
                    // 4 lessons per module, posisi 1..4, module_id pasti benar
                    $lessons = CourseLesson::factory()
                        ->count(4)
                        ->sequence(
                            ['module_id' => $module->id, 'position' => 1],
                            ['module_id' => $module->id, 'position' => 2],
                            ['module_id' => $module->id, 'position' => 3],
                            ['module_id' => $module->id, 'position' => 4],
                        )
                        ->create();

                    $totalLessons += $lessons->count();
                    $totalDuration += $lessons->sum('duration_seconds');
                }

                $course->update([
                    'lessons_count' => $totalLessons,
                    'duration_seconds' => $totalDuration,
                ]);
            });

        $this->command->info('✅ Seeded: courses + modules + lessons (FK valid, posisi berurutan).');
    }
}
