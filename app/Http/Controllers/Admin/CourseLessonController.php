<?php

// app/Http/Controllers/Admin/CourseLessonController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\CourseModule;
use App\Models\Lms\CourseLesson;
use Illuminate\Http\Request;

class CourseLessonController extends Controller
{
    public function index(Course $course, CourseModule $module)
    {
        abort_unless($module->course_id === $course->id, 404);
        $lessons = $module->lessons()->orderBy('position')->get();
        return response()->json(['success' => true, 'data' => $lessons]);
    }

    public function store(Request $request, Course $course, CourseModule $module)
    {
        abort_unless($module->course_id === $course->id, 404);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:180'],
            'content_type' => ['required', 'in:Text,Quiz,Video'],
            'content'      => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $position = (int) $module->lessons()->max('position') + 1;

        $lesson = $module->lessons()->create($data + [
            'position'     => $position,
            'is_published' => false,
        ]);

        return response()->json(['success' => true, 'data' => $lesson], 201);
    }

    public function update(Request $request, Course $course, CourseModule $module, CourseLesson $lesson)
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:180'],
            'content_type' => ['required', 'in:Text,Quiz,Video'],
            'content'      => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $lesson->update($data);
        return response()->json(['success' => true, 'data' => $lesson]);
    }

    public function destroy(Course $course, CourseModule $module, CourseLesson $lesson)
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);
        $lesson->delete();
        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Course $course, CourseModule $module)
    {
        abort_unless($module->course_id === $course->id, 404);

        // payload: [{id, position}, ...]
        $items = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'string'],
            'items.*.position' => ['required', 'integer', 'min:1'],
        ])['items'];

        \DB::transaction(function () use ($items, $module) {
            foreach ($items as $it) {
                $module->lessons()->whereKey($it['id'])->update(['position' => $it['position']]);
            }
        });

        return response()->json(['success' => true]);
    }
}
