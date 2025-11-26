<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Lms\Category;
use App\Models\Lms\Course;
use App\Models\Lms\Tag;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $categoryId = $request->category_id ?: null;          // int/null
        $instructorId = $request->integer('instructor_id') ?: null;
        $sort = $request->input('sort', 'date_desc');

        $validStatuses = ['draft', 'published', 'archived'];
        if ($status && ! in_array($status, $validStatuses, true)) {
            return back()->withErrors(['status' => 'Status tidak valid.']);
        }

        $validSorts = ['date_desc', 'date_asc', 'title_asc', 'title_desc', 'status_asc', 'status_desc'];
        if (! in_array($sort, $validSorts, true)) {
            $sort = 'date_desc';
        }

        $courses = Course::query()
            ->with(['categories:id,name', 'instructor:id,name'])
            ->withCount(['modules', 'lessons', 'enrollments'])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('subtitle', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($status, fn ($qq) => $qq->where('status', $status))
            ->when($categoryId, function ($qq) use ($categoryId) {
                $qq->whereHas('categories', function ($w) use ($categoryId) {
                    $w->where('categories.id', (int) $categoryId);
                });
            })
            ->when($instructorId, fn ($qq) => $qq->where('instructor_id', $instructorId))
            ->when($sort, function ($qq) use ($sort) {
                return match ($sort) {
                    'date_asc' => $qq->orderBy('created_at', 'asc'),
                    'date_desc' => $qq->orderBy('created_at', 'desc'),
                    'title_asc' => $qq->orderBy('title', 'asc'),
                    'title_desc' => $qq->orderBy('title', 'desc'),
                    'status_asc' => $qq->orderBy('status', 'asc')->orderBy('created_at', 'desc'),
                    'status_desc' => $qq->orderBy('status', 'desc')->orderBy('created_at', 'desc'),
                    default => $qq->orderBy('created_at', 'desc'),
                };
            }, fn ($qq) => $qq->orderBy('created_at', 'desc'))
            ->paginate(12)
            ->withQueryString();

        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $instructors = User::select('id', 'name')->whereHas('roles', function ($q) {
            $q->where('name', 'instructor');
        })->orderBy('name')->get();

        $stats = [
            'total' => Course::count(),
            'published' => Course::where('status', 'published')->count(),
            'draft' => Course::where('status', 'draft')->count(),
            'archived' => Course::where('status', 'archived')->count(),
        ];

        return view('admin.pages.courses.index', compact(
            'courses',
            'categories',
            'instructors',
            'q',
            'status',
            'categoryId',
            'instructorId',
            'sort',
            'stats'
        ));
    }

    public function create()
    {
        $categories = Category::with('createdBy.roles')->orderBy('name')->get();
        $tags = Tag::select('id', 'name')->orderBy('name')->get();
        $instructors = User::select('id', 'name')->whereHas('roles', function ($q) {
            $q->where('name', 'instructor');
        })->orderBy('name')->get();

        return view('admin.pages.courses.create-builder', compact('categories', 'instructors', 'tags'));
    }

    public function edit(Course $course)
    {
        $course->load([
            'categories', // Load full relation to access createdBy
            'instructor:id,name',
            'modules' => fn ($q) => $q->orderBy('order'),
            'modules.lessons' => fn ($q) => $q->orderBy('order_no'),
            'modules.lessons.quiz',
            'tags:id,name',
        ]);

        // Ensure categories have createdBy loaded if not covered by course load (though we fetch all categories separately below)
        $categories = Category::with('createdBy.roles')->orderBy('name')->get();
        $tags = Tag::select('id', 'name')->orderBy('name')->get();
        $instructors = User::select('id', 'name')->orderBy('name')->get();

        return view('admin.pages.courses.create-builder', compact('categories', 'instructors', 'course', 'tags'));
    }

    /**
     * Store menggunakan Form POST biasa dan redirect.
     */
    public function store(CourseRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $thumb = $request->hasFile('thumbnail')
                ? $request->file('thumbnail')->store('courses', 'public')
                : null;

            // Ambil difficulty dari form baru; fallback ke 'level' untuk kompatibilitas lama.
            $difficulty = $data['difficulty'] ?? $data['level'] ?? 'beginner';
            $categoryId = isset($data['category_id']) ? $data['category_id'] : null;
            $instructorId = $data['instructor_id'] ?? null;

            // --- NEW: flags pre/post & requirement dari checkbox ---
            $hasPre = $request->boolean('has_pretest');
            $hasPost = $request->boolean('has_posttest');
            $hasDueDate = $request->boolean('using_due_date');
            $reqBefore = $request->boolean('require_pretest_before_content');

            $course = Course::create([
                'id' => (string) Str::uuid(),
                'title' => $data['title'],
                'slug' => Str::slug($data['title']).'-'.Str::random(5),
                'subtitle' => $data['subtitle'] ?? null,
                'description' => $data['description'],
                'thumbnail_path' => $thumb,
                'status' => 'draft',
                'difficulty' => $difficulty,
                'instructor_id' => $instructorId,

                // --- NEW fields ---
                'has_pretest' => $hasPre,
                'has_posttest' => $hasPost,
                'require_pretest_before_content' => $reqBefore,
                'created_by' => Auth::user()->id,
                'using_due_date' => $hasDueDate,
            ]);

            if ($categoryId) {
                $course->categories()->sync([$categoryId]);
            } else {
                $course->categories()->sync([]);
            }

            if (isset($data['tags'])) {
                $course->tags()->sync($data['tags']);
            }

            DB::commit();

            return redirect()
                ->route('admin.courses.edit', $course->id)
                ->with('success', 'Kursus dasar "'.$course->title.'" berhasil dibuat. Silakan tambahkan Modul dan Pelajaran.');
        } catch (Throwable $e) {
            DB::rollBack();

            if (isset($thumb) && $thumb && Storage::disk('public')->exists($thumb)) {
                Storage::disk('public')->delete($thumb);
            }

            Log::error('Gagal membuat kursus baru', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat kursus: '.$e->getMessage());
        }
    }

    /**
     * Update menggunakan Form POST biasa dan redirect.
     */
    public function update(CourseRequest $request, Course $course)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // === Thumbnail baru (opsional) ===
            if ($request->hasFile('thumbnail')) {
                $oldThumb = $course->thumbnail_path;
                $newThumb = $request->file('thumbnail')->store('courses', 'public');
                $course->thumbnail_path = $newThumb;

                if (! empty($oldThumb) && Storage::disk('public')->exists($oldThumb)) {
                    Storage::disk('public')->delete($oldThumb);
                }
            }

            // === Ambil nilai umum dari form ===
            $difficulty = $data['difficulty'] ?? $data['level'] ?? $course->difficulty;
            $instructorId = $data['instructor_id'] ?? $course->instructor_id;

            // === Ambil dan normalisasi flag baru dari checkbox ===
            $hasPre = $request->boolean('has_pretest');
            $hasPost = $request->boolean('has_posttest');
            $reqBefore = $request->boolean('require_pretest_before_content');
            $hasDueDate = $request->boolean('using_due_date');

            // Jika pretest dimatikan, requirement wajib pretest juga harus padam
            if (! $hasPre && $reqBefore) {
                $reqBefore = false;
                // optional: beri info ringan
                // session()->flash('info', 'Opsi "Wajib pretest sebelum konten" dimatikan karena Pretest nonaktif.');
            }

            // === Update kolom course ===
            $course->fill([
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'description' => $data['description'],
                'difficulty' => $difficulty,
                'instructor_id' => $instructorId,

                // kolom baru
                'has_pretest' => $hasPre,
                'has_posttest' => $hasPost,
                'require_pretest_before_content' => $reqBefore,
                'using_due_date' => $hasDueDate,

            ])->save();

            // === Sinkron kategori ===
            $categoryId = $data['category_id'] ?? null;
            $course->categories()->sync($categoryId ? [$categoryId] : []);

            // === Sinkron tags ===
            if (isset($data['tags'])) {
                $course->tags()->sync($data['tags']);
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Pengaturan Kursus "'.$course->title.'" berhasil diperbarui.');
        } catch (Throwable $e) {
            DB::rollBack();

            if (isset($newThumb) && Storage::disk('public')->exists($newThumb)) {
                Storage::disk('public')->delete($newThumb);
            }

            Log::error('Gagal memperbarui kursus', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui kursus: '.$e->getMessage());
        }
    }

    public function updateStatus(Request $request, Course $course)
    {
        $request->validate([
            'status' => 'required|in:published,draft',
        ]);

        if ($request->status === 'published') {
            return $this->publish($request, $course);
        }

        try {
            $course->update(['status' => 'draft']);

            UserActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'unpublish_course',
                'subject_type' => Course::class,
                'subject_id' => $course->id,
                'description' => "Unpublished course: {$course->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Kursus berhasil diubah menjadi Draft.');
        } catch (Throwable $e) {
            Log::error('Gagal unpublish kursus', ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal mengubah status: '.$e->getMessage());
        }
    }

    /**
     * Publish menggunakan Form POST biasa dan redirect.
     */
    public function publish(Request $request, Course $course)
    {
        try {
            DB::beginTransaction();

            // Kunci baris course agar aman dari race condition
            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($fresh->status === 'published') {
                DB::commit();

                return back()->with('info', 'Kursus sudah dipublikasikan sebelumnya.');
            }

            // Muat relasi yang diperlukan untuk validasi
            $fresh->load(['modules.lessons.quiz']);

            // ✅ Validasi: minimal 1 module
            if ($fresh->modules->isEmpty()) {
                DB::rollBack();

                return back()->with('error', 'Kursus harus memiliki minimal 1 modul sebelum dipublikasikan.');
            }

            // ✅ Validasi: minimal 1 lesson total
            $totalLessons = $fresh->modules->sum(fn ($m) => $m->lessons->count());
            if ($totalLessons < 1) {
                DB::rollBack();

                return back()->with('error', 'Setiap kursus harus memiliki minimal 1 pelajaran (lesson) sebelum dipublikasikan.');
            }

            // (Opsional) Jika nanti toggle using_pretest / using_posttest disimpan ke DB,
            // tambahkan guard di sini untuk memastikan pretest/posttest sudah dibuat.

            $fresh->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Kursus berhasil dipublikasikan!');
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Gagal publish kursus', [
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat mempublikasikan kursus: '.$e->getMessage());
        }
    }

    public function destroy(Course $course)
    {
        try {
            DB::beginTransaction();

            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Cek: masih ada user yang ter-assign/enrolled?
            $isAssigned = $fresh->enrollments()->exists();

            if ($isAssigned) {
                DB::rollBack();

                return back()->with('error', 'Kursus tidak bisa dihapus karena masih ada pengguna yang terdaftar/enrolled. Cabut enrollment terlebih dulu.');
            }

            $title = $fresh->title;
            $oldThumb = $fresh->thumbnail_path;

            // Lepas pivot kategori
            $fresh->categories()->detach();

            // Hapus anak jika belum pakai FK cascade di DB (opsional)
            $fresh->lessons()->delete();
            $fresh->modules()->delete();
            $fresh->enrollments()->delete();

            // Hapus course
            $fresh->delete();

            DB::commit();

            if ($oldThumb && Storage::disk('public')->exists($oldThumb)) {
                Storage::disk('public')->delete($oldThumb);
            }

            return redirect()
                ->route('admin.courses.index')
                ->with('success', 'Course "'.$title.'" berhasil dihapus.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat menghapus kursus: '.$e->getMessage());
        }
    }
}
