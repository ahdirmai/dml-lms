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

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $q              = trim((string) $request->input('q', ''));
        $status         = $request->input('status');
        $categoryId     = $request->integer('category_id') ?: null;
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
            ->withCount(['modules', 'lessons'])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('subtitle', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($categoryId, function ($qq) use ($categoryId) {
                $qq->whereHas('categories', fn($w) => $w->where('categories.id', $categoryId));
            })
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
        $instructors = User::select('id', 'name')->orderBy('name')->get();

        return view('admin.pages.courses.index', compact('courses', 'categories', 'instructors', 'q', 'status', 'categoryId', 'instructorId', 'sort'));
    }

    public function create()
    {
        $categories  = Category::select('id', 'name')->orderBy('name')->get();
        $instructors = User::select('id', 'name')->orderBy('name')->get();
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

        // Redirect ke halaman builder setelah CREATE
        return redirect()->route('admin.courses.edit', $course->id)
            ->with('success', 'Kursus dasar "' . $course->title . '" berhasil dibuat. Silakan tambahkan Modul dan Pelajaran.');
    }

    /**
     * Update menggunakan Form POST biasa dan redirect.
     */
    public function update(CourseRequest $request, Course $course)
    {
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_path) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }
            $course->thumbnail_path = $request->file('thumbnail')->store('courses', 'public');
        }

        $course->fill([
            'title'         => $data['title'],
            'subtitle'      => $data['subtitle'] ?? null,
            'description'   => $data['description'],
            'difficulty'    => $data['level'] ?? $course->difficulty,
            'instructor_id' => $data['instructor_id'],
        ])->save();

        $course->categories()->sync([$data['category_id']]);

        // Redirect kembali ke halaman yang sama (builder) setelah UPDATE
        return redirect()->back()
            ->with('success', 'Pengaturan Kursus "' . $course->title . '" berhasil diperbarui.');
    }

    /**
     * Publish menggunakan Form POST biasa dan redirect.
     */
    public function publish(Request $request, Course $course)
    {
        $course->load(['modules.lessons.quiz']);

        // ✅ Validasi: minimal 1 module
        if ($course->modules->isEmpty()) {
            return redirect()->back()->with('error', 'Kursus harus memiliki minimal 1 modul sebelum dipublikasikan.');
        }

        // ✅ Validasi: minimal 1 lesson di total semua modul
        $totalLessons = $course->modules->sum(fn($module) => $module->lessons->count());
        if ($totalLessons < 1) {
            return redirect()->back()->with('error', 'Setiap kursus harus memiliki minimal 1 pelajaran (lesson) sebelum dipublikasikan.');
        }

        // ✅ Jika valid, ubah status
        $course->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Kursus berhasil dipublikasikan!');
    }


    public function destroy(Course $course)
    {
        if ($course->thumbnail_path) {
            Storage::disk('public')->delete($course->thumbnail_path);
        }
        $title = $course->title;
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course "' . $title . '" berhasil dihapus.');
    }

    /**
     * Hapus AJAX endpoint yang tidak digunakan lagi.
     * showData dihapus.
     */
}
