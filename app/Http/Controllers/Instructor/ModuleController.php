<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ModuleController extends Controller
{
    /**
     * Store Module menggunakan Form POST biasa dan redirect.
     */
    public function store(Request $request, Course $course)
    {
        // hanya pemilik course yg boleh menambah modul
        abort_unless($course->instructor_id === Auth::id(), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
        ]);

        try {
            DB::beginTransaction();

            // Kunci course agar perhitungan order aman
            $freshCourse = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            $maxOrder = (int) (Module::where('course_id', $freshCourse->id)->max('order') ?? 0);

            $module = Module::create([
                'id'        => (string) Str::uuid(),
                'course_id' => $freshCourse->id,
                'title'     => $data['title'],
                'order'     => $maxOrder + 1,
            ]);

            DB::commit();

            return redirect()
                ->route('instructor.courses.edit', $freshCourse->id)
                ->with('success', 'Modul "' . $module->title . '" berhasil dibuat.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat modul: ' . $e->getMessage());
        }
    }

    /**
     * Update Module menggunakan Form POST biasa dan redirect.
     */
    public function update(Request $request, Module $module)
    {
        // hanya pemilik course yg boleh update modul
        abort_unless(optional($module->course)->instructor_id === Auth::id(), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
        ]);

        try {
            DB::beginTransaction();

            // Kunci module agar aman dari update bersamaan
            $freshModule = Module::query()
                ->whereKey($module->id)
                ->lockForUpdate()
                ->firstOrFail();

            $freshModule->update(['title' => $data['title']]);

            DB::commit();

            return redirect()
                ->route('instructor.courses.edit', $freshModule->course_id)
                ->with('success', 'Modul "' . $freshModule->title . '" berhasil diperbarui.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui modul: ' . $e->getMessage());
        }
    }

    /**
     * Delete Module menggunakan Form POST biasa dan redirect.
     */
    public function destroy(Module $module)
    {
        // hanya pemilik course yg boleh hapus modul
        abort_unless(optional($module->course)->instructor_id === Auth::id(), 403);

        try {
            DB::beginTransaction();

            // Kunci module agar tidak balapan dengan reorder/lesson CRUD
            $freshModule = Module::query()
                ->whereKey($module->id)
                ->lockForUpdate()
                ->firstOrFail();

            $courseId = $freshModule->course_id;
            $title    = $freshModule->title;

            // Jika belum pakai FK CASCADE utk lessons, aktifkan penghapusan anak manual:
            // $freshModule->lessons()->delete();

            $freshModule->delete();

            DB::commit();

            return redirect()
                ->route('instructor.courses.edit', $courseId)
                ->with('success', 'Modul "' . $title . '" berhasil dihapus.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus modul: ' . $e->getMessage());
        }
    }

    /**
     * Reorder Module menggunakan Form POST biasa dan redirect.
     */
    public function reorder(Request $request, Course $course)
    {
        // hanya pemilik course yg boleh reorder modul
        abort_unless($course->instructor_id === Auth::id(), 403);

        $data = $request->validate([
            'orders'           => ['required', 'array'],
            'orders.*.id'      => ['required', 'string'],
            'orders.*.order'   => ['required', 'integer'],
        ]);

        try {
            DB::beginTransaction();

            // Kunci course agar konsisten saat set urutan
            $freshCourse = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            foreach ($data['orders'] as $row) {
                Module::query()
                    ->where('id', $row['id'])
                    ->where('course_id', $freshCourse->id)
                    ->update(['order' => (int) $row['order']]);
            }

            DB::commit();

            return back()->with('success', 'Urutan modul berhasil disimpan.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan urutan modul: ' . $e->getMessage());
        }
    }
}
