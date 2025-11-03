<?php

// app/Http/Controllers/Admin/CourseModuleController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\CourseModule;
use Illuminate\Http\Request;

class CourseModuleController extends Controller
{
    public function index(Course $course)
    {
        $modules = $course->modules()->with('lessons')->orderBy('position')->get();
        return response()->json(['success' => true, 'data' => $modules]);
    }

    public function store(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
        ]);
        $position = (int) $course->modules()->max('position') + 1;

        $module = $course->modules()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'position' => $position,
            'is_published' => false,
        ]);

        return response()->json(['success' => true, 'data' => $module], 201);
    }

    public function update(Request $request, Course $course, CourseModule $module)
    {
        abort_unless($module->course_id === $course->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $module->update($data);
        return response()->json(['success' => true, 'data' => $module]);
    }

    public function destroy(Course $course, CourseModule $module)
    {
        abort_unless($module->course_id === $course->id, 404);
        $module->delete();
        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Course $course)
    {
        // payload: [{id, position}, ...]
        $items = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'string'],
            'items.*.position' => ['required', 'integer', 'min:1'],
        ])['items'];

        \DB::transaction(function () use ($items, $course) {
            foreach ($items as $it) {
                $course->modules()->whereKey($it['id'])->update(['position' => $it['position']]);
            }
        });

        return response()->json(['success' => true]);
    }
}
