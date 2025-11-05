<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Lms\Course;
use App\Models\Lms\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $q              = trim((string) $request->input('q', ''));
        $status         = $request->input('status');
        // ✅ Mengambil nilai integer dari request
        $categoryId     = $request->category_id ?: null;
        $instructorId   = $request->integer('instructor_id') ?: null;
        $sort           = $request->input('sort', 'date_desc');

        $validStatuses = ['draft', 'published', 'archived'];
        if ($status && ! in_array($status, $validStatuses, true)) {
            return back()->withErrors(['status' => 'Status tidak valid.']);
        }
        $validSorts = ['date_desc', 'date_asc', 'title_asc', 'title_desc', 'status_asc', 'status_desc'];
        if (!in_array($sort, $validSorts, true)) {
            $sort = 'date_desc';
        }

        $courses = Course::query()
            ->with(['categories:id,name', 'instructor:id,name'])
            ->withCount(['modules', 'lessons', 'students']) // Tambah students
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('subtitle', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))

            // 🚀 BAGIAN PERBAIKAN CATEGORY FILTER
            ->when($categoryId, function ($qq) use ($categoryId) {
                $qq->whereHas('categories', function ($w) use ($categoryId) {
                    // Pastikan nilai yang dibandingkan adalah integer
                    $w->where('categories.id',  $categoryId);
                });
            })
            // 🚀 AKHIR PERBAIKAN

            ->when($instructorId, fn($qq) => $qq->where('instructor_id', $instructorId))
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
        $instructors = User::select('id', 'name')->whereHas('roles', function ($q) {
            $q->where('name', 'instructor');
        })->orderBy('name')->get();
        return view('admin.pages.courses.index', compact('courses', 'categories', 'instructors', 'q', 'status', 'categoryId', 'instructorId', 'sort'));
    }

    public function create()
    {
        $categories  = Category::select('id', 'name')->orderBy('name')->get();
        $instructors = User::select('id', 'name')->whereHas('roles', function ($q) {
            $q->where('name', 'instructor');
        })->orderBy('name')->get();
        return view('admin.pages.courses.create-builder', compact('categories', 'instructors'));
    }

    public function edit(Course $course)
    {
        $course->load([
            'categories:id,name',
            'instructor:id,name',
            'modules' => fn($q) => $q->orderBy('order'),
            'modules.lessons' => fn($q) => $q->orderBy('order'),
            'modules.lessons.quiz',
        ]);

        $categories  = Category::select('id', 'name')->orderBy('name')->get();
        $instructors = User::select('id', 'name')->orderBy('name')->get();
        return view('admin.pages.courses.create-builder', compact('categories', 'instructors', 'course'));
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

            $course = Course::create([
                'id'             => (string) Str::uuid(),
                'title'          => $data['title'],
                'slug'           => Str::slug($data['title']) . '-' . Str::random(5),
                'subtitle'       => $data['subtitle'] ?? null,
                'description'    => $data['description'],
                'thumbnail_path' => $thumb,
                'status'         => 'draft',
                'difficulty'     => $data['level'] ?? 'beginner',
                'instructor_id'  => $data['instructor_id'],
            ]);

            $course->categories()->sync([$data['category_id']]);

            DB::commit();

            return redirect()
                ->route('admin.courses.edit', $course->id)
                ->with('success', 'Kursus dasar "' . $course->title . '" berhasil dibuat. Silakan tambahkan Modul dan Pelajaran.');
        } catch (Throwable $e) {
            DB::rollBack();

            // Jika thumbnail sudah terupload tapi gagal di DB, hapus file-nya
            if (isset($thumb) && $thumb && Storage::disk('public')->exists($thumb)) {
                Storage::disk('public')->delete($thumb);
            }

            // Logging optional (bisa kamu aktifkan)
            Log::error('Gagal membuat kursus baru', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat kursus: ' . $e->getMessage());
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

            // Jika ada thumbnail baru
            if ($request->hasFile('thumbnail')) {
                // Simpan path lama untuk jaga-jaga jika rollback
                $oldThumb = $course->thumbnail_path;

                // Upload thumbnail baru
                $newThumb = $request->file('thumbnail')->store('courses', 'public');

                // Update ke model
                $course->thumbnail_path = $newThumb;

                // Hapus file lama (setelah upload baru sukses)
                if ($oldThumb && Storage::disk('public')->exists($oldThumb)) {
                    Storage::disk('public')->delete($oldThumb);
                }
            }

            $course->fill([
                'title'         => $data['title'],
                'subtitle'      => $data['subtitle'] ?? null,
                'description'   => $data['description'],
                'difficulty'    => $data['level'] ?? $course->difficulty,
                'instructor_id' => $data['instructor_id'],
            ])->save();

            $course->categories()->sync([$data['category_id']]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Pengaturan Kursus "' . $course->title . '" berhasil diperbarui.');
        } catch (Throwable $e) {
            DB::rollBack();

            // Jika file baru sudah diupload tapi DB gagal, hapus file baru agar tidak menumpuk
            if (isset($newThumb) && Storage::disk('public')->exists($newThumb)) {
                Storage::disk('public')->delete($newThumb);
            }

            // Log error opsional
            Log::error('Gagal memperbarui kursus', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui kursus: ' . $e->getMessage());
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

            // Jika sudah published, tidak perlu lanjut
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

            // ✅ Validasi: minimal 1 lesson di total semua modul
            $totalLessons = $fresh->modules->sum(fn($m) => $m->lessons->count());
            if ($totalLessons < 1) {
                DB::rollBack();
                return back()->with('error', 'Setiap kursus harus memiliki minimal 1 pelajaran (lesson) sebelum dipublikasikan.');
            }

            // ✅ Jika valid, ubah status
            $fresh->update([
                'status'       => 'published',
                'published_at' => now(),
            ]);

            DB::commit();

            // TODO: Jika ada user yang sudah di-assign, kirim email pemberitahuan (dispatch job/event)

            return back()->with('success', 'Kursus berhasil dipublikasikan!');
        } catch (Throwable $e) {
            DB::rollBack();

            // Log opsional:
            Log::error('Gagal publish kursus', ['course_id' => $course->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Terjadi kesalahan saat mempublikasikan kursus: ' . $e->getMessage());
        }
    }


    public function destroy(Course $course)
    {
        try {
            DB::beginTransaction();

            // Kunci baris course untuk mencegah race condition
            $fresh = Course::query()
                ->whereKey($course->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Cek: masih ada user yang ter-assign/enrolled?
            $isAssigned = $fresh->enrollments()->exists();
            // Atau jika kamu ingin double-check via pivot convenience:
            // $isAssigned = $isAssigned || $fresh->students()->exists();

            if ($isAssigned) {
                DB::rollBack();
                return back()->with('error', 'Kursus tidak bisa dihapus karena masih ada pengguna yang terdaftar/enrolled. Cabut enrollment terlebih dulu.');
            }

            $title    = $fresh->title;
            $oldThumb = $fresh->thumbnail_path;

            // Lepas pivot kategori (jika belum pakai FK cascade di pivot)
            $fresh->categories()->detach();

            // Hapus anak jika belum pakai FK cascade di DB (opsional, aktifkan bila perlu)
            $fresh->lessons()->delete();
            $fresh->modules()->delete();
            $fresh->enrollments()->delete(); // biasanya kosong karena sudah dicek, tapi aman untuk berjaga

            // Hapus course
            $fresh->delete();

            DB::commit();

            // Bersihkan file thumbnail hanya setelah commit sukses
            if ($oldThumb && Storage::disk('public')->exists($oldThumb)) {
                Storage::disk('public')->delete($oldThumb);
            }

            return redirect()
                ->route('admin.courses.index')
                ->with('success', 'Course "' . $title . '" berhasil dihapus.');
        } catch (Throwable $e) {
            DB::rollBack();

            // Log opsional:
            // Log::error('Gagal menghapus course', ['course_id' => $course->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Terjadi kesalahan saat menghapus kursus: ' . $e->getMessage());
        }
    }

    /**
     * Hapus AJAX endpoint yang tidak digunakan lagi.
     * showData dihapus.
     */
}
