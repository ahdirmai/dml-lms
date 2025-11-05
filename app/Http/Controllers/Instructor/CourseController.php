<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Lms\Course;
use App\Models\Lms\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $q            = trim((string) $request->input('q', ''));
        $status       = $request->input('status');
        $categoryId   = $request->category_id ?: null;
        $sort         = $request->input('sort', 'date_desc');

        $validStatuses = ['draft', 'published', 'archived'];
        if ($status && !in_array($status, $validStatuses, true)) {
            return back()->withErrors(['status' => 'Status tidak valid.']);
        }
        $validSorts = ['date_desc', 'date_asc', 'title_asc', 'title_desc', 'status_asc', 'status_desc'];
        if (!in_array($sort, $validSorts, true)) {
            $sort = 'date_desc';
        }

        $courses = Course::query()
            ->with(['categories:id,name', 'instructor:id,name'])
            ->withCount(['modules', 'lessons'])
            ->where('instructor_id', Auth::id()) // hanya kursus milik instruktur
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('subtitle', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($categoryId, fn($qq) => $qq->whereHas('categories', fn($w) => $w->where('categories.id', $categoryId)))
            ->when($sort, function ($qq) use ($sort) {
                return match ($sort) {
                    'date_asc'      => $qq->orderBy('created_at', 'asc'),
                    'date_desc'     => $qq->orderBy('created_at', 'desc'),
                    'title_asc'     => $qq->orderBy('title', 'asc'),
                    'title_desc'    => $qq->orderBy('title', 'desc'),
                    'status_asc'    => $qq->orderBy('status', 'asc')->orderBy('created_at', 'desc'),
                    'status_desc'   => $qq->orderBy('status', 'desc')->orderBy('created_at', 'desc'),
                    default         => $qq->orderBy('created_at', 'desc'),
                };
            }, fn($qq) => $qq->orderBy('created_at', 'desc'))
            ->paginate(12)
            ->withQueryString();

        $categories  = Category::select('id', 'name')->orderBy('name')->get();

        return view('instructor.pages.courses.index', compact('courses', 'categories', 'q', 'status', 'categoryId', 'sort'));
    }

    public function create()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        return view('instructor.pages.courses.create-builder', compact('categories'));
    }

    public function edit(Course $course)
    {
        abort_unless($course->instructor_id === Auth::id(), 403);

        $course->load([
            'categories:id,name',
            'instructor:id,name',
            'modules' => fn($q) => $q->orderBy('order'),
            'modules.lessons' => fn($q) => $q->orderBy('order'),
            'modules.lessons.quiz',
        ]);

        $categories = Category::select('id', 'name')->orderBy('name')->get();
        return view('instructor.pages.courses.create-builder', compact('categories', 'course'));
    }

    /**
     * Store (Form POST biasa + redirect) — dengan transaksi & cleanup file aman.
     */
    public function store(CourseRequest $request)
    {
        $data = $request->validated();

        $instructorId = $data['instructor_id'] ?? Auth::id();
        // Default-kan instructor_id ke Auth::id() (instruktur tidak perlu memilih dirinya sendiri)

        $newThumb = null;
        try {
            DB::beginTransaction();

            if ($request->hasFile('thumbnail')) {
                $newThumb = $request->file('thumbnail')->store('courses', 'public');
            }

            $course = Course::create([
                'id'             => (string) Str::uuid(),
                'title'          => $data['title'],
                'slug'           => Str::slug($data['title']) . '-' . Str::random(5),
                'subtitle'       => $data['subtitle'] ?? null,
                'description'    => $data['description'],
                'thumbnail_path' => $newThumb,
                'status'         => 'draft',
                'difficulty'     => $data['level'] ?? 'beginner',
                'instructor_id'  => $instructorId,
            ]);

            $course->categories()->sync([$data['category_id']]);

            DB::commit();

            return redirect()
                ->route('instructor.courses.edit', $course->id)
                ->with('success', 'Kursus dasar "' . $course->title . '" berhasil dibuat. Silakan tambahkan Modul dan Pelajaran.');
        } catch (Throwable $e) {
            DB::rollBack();

            // Hapus file yang terlanjur terupload
            if ($newThumb && Storage::disk('public')->exists($newThumb)) {
                Storage::disk('public')->delete($newThumb);
            }

            return back()->withInput()->with('error', 'Gagal membuat kursus: ' . $e->getMessage());
        }
    }

    /**
     * Update (Form POST biasa + redirect) — dengan transaksi, lock, dan rollback file aman.
     */
    public function update(CourseRequest $request, Course $course)
    {
        abort_unless($course->instructor_id === Auth::id(), 403);

        $data = $request->validated();

        $oldThumb = $course->thumbnail_path;
        $newThumb = null;

        try {
            DB::beginTransaction();

            // Lock record
            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->hasFile('thumbnail')) {
                $newThumb = $request->file('thumbnail')->store('courses', 'public');
                $fresh->thumbnail_path = $newThumb; // hapus lama setelah commit
            }

            $fresh->fill([
                'title'         => $data['title'],
                'subtitle'      => $data['subtitle'] ?? null,
                'description'   => $data['description'],
                'difficulty'    => $data['level'] ?? $fresh->difficulty,
                'instructor_id' => $fresh->instructor_id, // instruktur tetap pemilik
            ])->save();

            $fresh->categories()->sync([$data['category_id']]);

            DB::commit();

            // Hapus thumbnail lama setelah commit
            if ($newThumb && $oldThumb && Storage::disk('public')->exists($oldThumb)) {
                Storage::disk('public')->delete($oldThumb);
            }

            return back()->with('success', 'Pengaturan Kursus "' . $fresh->title . '" berhasil diperbarui.');
        } catch (Throwable $e) {
            DB::rollBack();

            // Hapus thumbnail baru jika gagal
            if ($newThumb && Storage::disk('public')->exists($newThumb)) {
                Storage::disk('public')->delete($newThumb);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui kursus: ' . $e->getMessage());
        }
    }

    /**
     * Publish — dengan transaksi & lock untuk cegah race condition.
     */
    public function publish(Request $request, Course $course)
    {
        abort_unless($course->instructor_id === Auth::id(), 403);

        try {
            DB::beginTransaction();

            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Jika sudah published
            if ($fresh->status === 'published') {
                DB::commit();
                return back()->with('info', 'Kursus sudah dipublikasikan sebelumnya.');
            }

            $fresh->load(['modules.lessons.quiz']);

            if ($fresh->modules->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'Kursus harus memiliki minimal 1 modul sebelum dipublikasikan.');
            }

            $totalLessons = $fresh->modules->sum(fn($m) => $m->lessons->count());
            if ($totalLessons < 1) {
                DB::rollBack();
                return back()->with('error', 'Setiap kursus harus memiliki minimal 1 pelajaran (lesson) sebelum dipublikasikan.');
            }

            $fresh->update([
                'status'       => 'published',
                'published_at' => $fresh->published_at ?? now(),
            ]);

            DB::commit();

            return back()->with('success', 'Kursus berhasil dipublikasikan!');
        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mempublikasikan kursus: ' . $e->getMessage());
        }
    }

    /**
     * Destroy — dengan transaksi, lock, cek enrollment, dan cleanup file setelah commit.
     */
    public function destroy(Course $course)
    {
        abort_unless($course->instructor_id === Auth::id(), 403);

        $oldThumb = $course->thumbnail_path;

        try {
            DB::beginTransaction();

            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Cegah hapus jika masih ada enrollment
            if ($fresh->enrollments()->exists()) {
                DB::rollBack();
                return back()->with('error', 'Kursus masih memiliki peserta terdaftar. Cabut enrollment terlebih dahulu.');
            }

            $title = $fresh->title;

            // Jika belum pakai FK cascade, detach pivot terlebih dulu (opsional):
            // $fresh->categories()->detach();
            // $fresh->modules()->each->delete(); // jika tidak cascade ke lessons

            $fresh->delete();

            DB::commit();

            // Bersihkan file setelah commit
            if ($oldThumb && Storage::disk('public')->exists($oldThumb)) {
                Storage::disk('public')->delete($oldThumb);
            }

            return redirect()->route('instructor.courses.index')->with('success', 'Course "' . $title . '" berhasil dihapus.');
        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus kursus: ' . $e->getMessage());
        }
    }
}
