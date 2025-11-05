<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    /**
     * Store Module menggunakan Form POST biasa dan redirect.
     */
    public function store(Request $request, Course $course)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:200']]);
        $maxOrder = (int) (Module::where('course_id', $course->id)->max('order') ?? 0);

        $module = Module::create([
            'id' => (string) Str::uuid(),
            'course_id' => $course->id,
            'title' => $data['title'],
            'order' => $maxOrder + 1,
        ]);

        // Redirect back ke builder
        return redirect()->route('admin.courses.edit', $course->id)
            ->with('success', 'Modul "' . $module->title . '" berhasil dibuat.');
    }

    /**
     * Update Module menggunakan Form POST biasa dan redirect.
     */
    public function update(Request $request, Module $module)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:200']]);
        $module->update(['title' => $data['title']]);

        // Redirect back ke builder
        return redirect()->route('admin.courses.edit', $module->course_id)
            ->with('success', 'Modul "' . $module->title . '" berhasil diperbarui.');
    }

    /**
     * Delete Module menggunakan Form POST biasa dan redirect.
     */
    public function destroy(Module $module)
    {
        $courseId = $module->course_id;
        $title = $module->title;
        $module->delete();

        // Redirect back ke builder
        return redirect()->route('admin.courses.edit', $courseId)
            ->with('success', 'Modul "' . $title . '" berhasil dihapus.');
    }

    /**
     * Reorder Module menggunakan Form POST biasa dan redirect.
     * (Reordering dengan non-AJAX membutuhkan form terpisah atau JS yang auto-submit)
     */
    public function reorder(Request $request, Course $course)
    {
        $data = $request->validate(['orders' => ['required', 'array']]);
        foreach ($data['orders'] as $row) {
            Module::where('id', $row['id'])->where('course_id', $course->id)->update(['order' => (int)$row['order']]);
        }
        return redirect()->back()
            ->with('success', 'Urutan modul berhasil disimpan.');
    }
}
