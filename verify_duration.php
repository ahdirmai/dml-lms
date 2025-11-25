<?php
use App\Models\Lms\Course;
use App\Models\Lms\Module;
use App\Models\Lms\Lesson;
use App\Models\User;
use Illuminate\Support\Str;

try {
    // Setup
    $user = User::first() ?? User::factory()->create();
    $course = Course::create([
        'id' => (string) Str::uuid(),
        'title' => 'Test Course Duration',
        'slug' => 'test-course-duration-' . Str::random(5),
        'description' => 'Test',
        'created_by' => $user->id,
    ]);
    $module = Module::create([
        'id' => (string) Str::uuid(),
        'course_id' => $course->id,
        'title' => 'Test Module',
        'order' => 1,
    ]);

    echo "Initial Course Duration: " . $course->refresh()->duration_seconds . "\n";

    // Create Lesson 1 (10 mins = 600s)
    $lesson1 = Lesson::create([
        'id' => (string) Str::uuid(),
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson 1',
        'kind' => 'youtube',
        'duration_seconds' => 600,
        'order_no' => 1,
    ]);
    // Simulate Controller logic: recalculate
    $total = Lesson::where('course_id', $course->id)->sum('duration_seconds');
    $course->update(['duration_seconds' => $total]);
    echo "After Lesson 1 (600s): " . $course->refresh()->duration_seconds . "\n";

    // Create Lesson 2 (20 mins = 1200s)
    $lesson2 = Lesson::create([
        'id' => (string) Str::uuid(),
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson 2',
        'kind' => 'gdrive',
        'duration_seconds' => 1200,
        'order_no' => 2,
    ]);
    $total = Lesson::where('course_id', $course->id)->sum('duration_seconds');
    $course->update(['duration_seconds' => $total]);
    echo "After Lesson 2 (1200s): " . $course->refresh()->duration_seconds . "\n";

    // Update Lesson 1 to 5 mins = 300s
    $lesson1->update(['duration_seconds' => 300]);
    $total = Lesson::where('course_id', $course->id)->sum('duration_seconds');
    $course->update(['duration_seconds' => $total]);
    echo "After Update Lesson 1 (300s): " . $course->refresh()->duration_seconds . "\n";

    // Delete Lesson 2
    $lesson2->delete();
    $total = Lesson::where('course_id', $course->id)->sum('duration_seconds');
    $course->update(['duration_seconds' => $total]);
    echo "After Delete Lesson 2: " . $course->refresh()->duration_seconds . "\n";

    // Cleanup
    $lesson1->delete();
    $module->delete();
    $course->delete();

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
