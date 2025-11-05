<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lms\Lesson;
use App\Models\Lms\Module;
use App\Support\LinkParsers; // Asumsi file ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class LessonController extends Controller
{
    /**
     * Store Lesson menggunakan Form POST biasa dan redirect.
     */
    public function store(Request $request, Module $module)
    {
        // hanya pemilik course yang boleh menambah lesson pada modul ini
        abort_unless($module->course && $module->course->instructor_id === Auth::id(), 403);

        // 1) Validasi
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'kind'        => ['required', 'in:youtube,gdrive,quiz'],
            'content_url' => ['nullable', 'url'],
        ]);

        try {
            DB::beginTransaction();

            // kunci module agar perhitungan order aman
            $freshModule = Module::query()
                ->whereKey($module->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 2) Parsing Content ID
            $ids = $this->parseSourceIds($data['kind'], $data['content_url'] ?? null);

            // 3) Tentukan order berikutnya (di dalam transaksi)
            $nextOrder = ((int) Lesson::where('module_id', $freshModule->id)->max('order')) + 1;

            // 4) Simpan Lesson
            $lesson = Lesson::create([
                'id'               => (string) Str::uuid(),
                'course_id'        => $freshModule->course_id,
                'module_id'        => $freshModule->id,
                'title'            => $data['title'],
                'description'      => $data['description'] ?? null,
                'kind'             => $data['kind'],
                'content_url'      => $data['content_url'] ?? null,
                'youtube_video_id' => $ids['youtube_video_id'] ?? null,
                'gdrive_file_id'   => $ids['gdrive_file_id'] ?? null,
                'order'            => $nextOrder,
            ]);

            DB::commit();

            // Redirect ke builder course
            return redirect()
                ->route('instructor.courses.edit', $freshModule->course_id)
                ->with('success', 'Pelajaran "' . $lesson->title . '" berhasil dibuat.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat pelajaran: ' . $e->getMessage());
        }
    }

    /**
     * Update Lesson menggunakan Form POST biasa dan redirect.
     */
    public function update(Request $request, Lesson $lesson)
    {
        // cek kepemilikan
        abort_unless($lesson->course && $lesson->course->instructor_id === Auth::id(), 403);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'kind'        => ['required', 'in:youtube,gdrive,quiz'],
            'content_url' => ['nullable', 'url'],
        ]);

        try {
            DB::beginTransaction();

            // kunci lesson agar aman dari update bersamaan
            $freshLesson = Lesson::query()
                ->whereKey($lesson->id)
                ->lockForUpdate()
                ->firstOrFail();

            $ids = $this->parseSourceIds($data['kind'], $data['content_url'] ?? null);

            $freshLesson->update([
                'title'            => $data['title'],
                'kind'             => $data['kind'],
                'content_url'      => $data['content_url'] ?? null,
                'youtube_video_id' => $ids['youtube_video_id'] ?? null,
                'gdrive_file_id'   => $ids['gdrive_file_id'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('instructor.courses.edit', $freshLesson->course_id)
                ->with('success', 'Pelajaran "' . $freshLesson->title . '" berhasil diperbarui.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pelajaran: ' . $e->getMessage());
        }
    }

    /**
     * Delete Lesson menggunakan Form POST biasa dan redirect.
     */
    public function destroy(Lesson $lesson)
    {
        // cek kepemilikan
        abort_unless($lesson->course && $lesson->course->instructor_id === Auth::id(), 403);

        try {
            DB::beginTransaction();

            // kunci lesson agar tidak balapan dengan reorder/update lain
            $freshLesson = Lesson::query()
                ->whereKey($lesson->id)
                ->lockForUpdate()
                ->firstOrFail();

            $courseId = $freshLesson->course_id;
            $title    = $freshLesson->title;

            $freshLesson->delete();

            DB::commit();

            return redirect()
                ->route('instructor.courses.edit', $courseId)
                ->with('success', 'Pelajaran "' . $title . '" berhasil dihapus.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus pelajaran: ' . $e->getMessage());
        }
    }

    /**
     * Reorder Lesson menggunakan Form POST biasa dan redirect.
     */
    public function reorder(Request $request, Module $module)
    {
        // cek kepemilikan
        abort_unless($module->course && $module->course->instructor_id === Auth::id(), 403);

        $data = $request->validate([
            'orders'           => ['required', 'array'],
            'orders.*.id'      => ['required', 'string'],
            'orders.*.order'   => ['required', 'integer'],
        ]);

        try {
            DB::beginTransaction();

            // kunci module agar konsisten
            $freshModule = Module::query()
                ->whereKey($module->id)
                ->lockForUpdate()
                ->firstOrFail();

            foreach ($data['orders'] as $row) {
                Lesson::query()
                    ->where('id', $row['id'])
                    ->where('module_id', $freshModule->id)
                    ->update(['order' => (int) $row['order']]);
            }

            DB::commit();

            return back()->with('success', 'Urutan pelajaran berhasil disimpan.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan urutan pelajaran: ' . $e->getMessage());
        }
    }

    private function parseSourceIds(string $kind, ?string $url): array
    {
        if (empty($url)) {
            return ['youtube_video_id' => null, 'gdrive_file_id' => null];
        }

        if ($kind === 'youtube') {
            $id = LinkParsers::parseYouTubeId($url);
            abort_if(!$id, 422, 'Invalid YouTube link');
            return ['youtube_video_id' => $id, 'gdrive_file_id' => null];
        }

        if ($kind === 'gdrive') {
            $id = LinkParsers::parseGDriveFileId($url);
            abort_if(!$id, 422, 'Invalid Google Drive link');
            return ['gdrive_file_id' => $id, 'youtube_video_id' => null];
        }

        // kind=quiz tidak butuh ID
        return ['youtube_video_id' => null, 'gdrive_file_id' => null];
    }
}
