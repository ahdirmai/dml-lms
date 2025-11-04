<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\Lesson;
use App\Models\Lms\Module;
use App\Support\LinkParsers; // Asumsi file ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    /**
     * Store Lesson menggunakan Form POST biasa dan redirect.
     */
    public function store(Request $request, Module $module)
    {
        // 1. Validasi Data
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'kind'          => ['required', 'in:youtube,gdrive,quiz'],
            'content_url'   => ['nullable', 'url'],
        ]);

        // 2. Parsing Content ID
        $ids = $this->parseSourceIds($data['kind'], $data['content_url'] ?? null);

        // 3. Simpan Lesson
        $lesson = Lesson::create([
            'id'               => (string) Str::uuid(),
            'course_id'        => $module->course_id,
            'module_id'        => $module->id,
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'kind'             => $data['kind'],
            'content_url'      => $data['content_url'] ?? null,
            'youtube_video_id' => $ids['youtube_video_id'] ?? null,
            'gdrive_file_id'   => $ids['gdrive_file_id'] ?? null,
            'order'            => (int) (Lesson::where('module_id', $module->id)->max('order') ?? 0) + 1,
        ]);

        // 4. Redirect ke halaman builder course setelah berhasil
        return redirect()->route('admin.courses.edit', $module->course_id)
            ->with('success', 'Pelajaran "' . $lesson->title . '" berhasil dibuat.');
    }

    /**
     * Update Lesson menggunakan Form POST biasa dan redirect.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'kind'          => ['required', 'in:youtube,gdrive,quiz'],
            'content_url'   => ['nullable', 'url'],
        ]);

        $ids = $this->parseSourceIds($data['kind'], $data['content_url'] ?? null);

        $lesson->update([
            'title'            => $data['title'],
            'kind'             => $data['kind'],
            'content_url'      => $data['content_url'] ?? null,
            'youtube_video_id' => $ids['youtube_video_id'] ?? null,
            'gdrive_file_id'   => $ids['gdrive_file_id'] ?? null,
        ]);

        // Redirect back ke builder
        return redirect()->route('admin.courses.edit', $lesson->course_id)
            ->with('success', 'Pelajaran "' . $lesson->title . '" berhasil diperbarui.');
    }

    /**
     * Delete Lesson menggunakan Form POST biasa dan redirect.
     */
    public function destroy(Lesson $lesson)
    {
        $courseId = $lesson->course_id;
        $title = $lesson->title;
        $lesson->delete();

        // Redirect back ke builder
        return redirect()->route('admin.courses.edit', $courseId)
            ->with('success', 'Pelajaran "' . $title . '" berhasil dihapus.');
    }

    /**
     * Reorder Lesson menggunakan Form POST biasa dan redirect.
     */
    public function reorder(Request $request, Module $module)
    {
        $data = $request->validate(['orders' => ['required', 'array']]);
        foreach ($data['orders'] as $row) {
            Lesson::where('id', $row['id'])->where('module_id', $module->id)->update(['order' => (int)$row['order']]);
        }
        return redirect()->back()
            ->with('success', 'Urutan pelajaran berhasil disimpan.');
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

        return [];
    }
}
