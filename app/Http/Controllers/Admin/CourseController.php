<?php
// app/Http/Controllers/Admin/CourseController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\Category;
use App\Models\Lms\Course;
use App\Models\Lms\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::query()
            ->with(['categories:id,name', 'tags:id,name'])
            ->when($q = $request->string('q')->toString(), function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('subtitle', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->get('status')))
            ->when($request->filled('visibility'), fn($qq) => $qq->where('visibility', $request->get('visibility')))
            ->when($request->filled('language'), fn($qq) => $qq->where('language', $request->get('language')))
            ->when($request->filled('level'), fn($qq) => $qq->where('level', $request->get('level')))
            ->when($request->filled('category_id'), fn($qq) => $qq->whereHas('categories', function ($c) use ($request) {
                $c->where('categories.id', $request->get('category_id'));
            }))
            ->when($request->filled('tag_id'), fn($qq) => $qq->whereHas('tags', function ($t) use ($request) {
                $t->where('tags.id', $request->get('tag_id'));
            }))
            ->latest('created_at');

        // Jika kamu ingin selalu akuratkan lessons_count tanpa kolom cached:
        // $query->withCount('lessons');

        $courses = $query->paginate(12);

        $stats = [
            'total'     => Course::count(),
            'published' => Course::where('status', 'published')->count(),
            'draft'     => Course::where('status', 'draft')->count(),
        ];
        $hasArchived = Course::where('status', 'archived')->exists();

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $tags = Tag::orderBy('name')->get(['id', 'name']);

        return view('admin.pages.courses.index', compact(
            'courses',
            'stats',
            'hasArchived',
            'categories',
            'tags'
        ));
    }


    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.pages.courses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:180'],
            'description'  => ['required', 'string'],
            'category_id'  => ['required', 'exists:categories,id'],
            'duration'     => ['required', 'integer', 'min:1'],
            'thumbnail'    => ['nullable', 'image', 'max:2048'], // <= 2MB
        ]);

        // unggah thumbnail jika ada
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('courses', 'public'); // storage/app/public/courses
        }

        $course = Course::create([
            'title'          => $data['title'],
            'description'    => $data['description'],
            'category_id'    => $data['category_id'],
            'duration'       => $data['duration'],
            'thumbnail_path' => $thumbnailPath,
            'status'         => 'draft',
            'instructor_id'  => $request->user()->id,  // pemilik/pengajar
        ]);

        // Save & Continue -> langsung ke builder
        if ($request->boolean('save_and_continue')) {
            return redirect()
                ->route('admin.courses.builder', $course)
                ->with('success', 'Course created. You can now add modules & lessons.');
        }

        return redirect()
            ->route('admin.courses.edit', $course)
            ->with('success', 'Course created.');
    }

    public function edit(Course $course)
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.pages.courses.edit', compact('course', 'categories'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:180'],
            'description'  => ['required', 'string'],
            'category_id'  => ['required', 'exists:categories,id'],
            'duration'     => ['required', 'integer', 'min:1'],
            'thumbnail'    => ['nullable', 'image', 'max:2048'],
            'status'       => ['nullable', Rule::in(['draft', 'published', 'archived'])],
        ]);

        // upload thumbnail baru (jika ada), tidak wajib saat update
        if ($request->hasFile('thumbnail')) {
            // optional: hapus yang lama
            if ($course->thumbnail_path && Storage::disk('public')->exists($course->thumbnail_path)) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }
            $data['thumbnail_path'] = $request->file('thumbnail')->store('courses', 'public');
        }

        // status opsional — kalau tidak dikirim, jangan diubah
        if (! array_key_exists('status', $data)) {
            unset($data['status']);
        }

        // field yang tidak ada di skema jangan dikirim
        unset($data['thumbnail']);

        $course->update($data);

        return back()->with('success', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        // optional: hapus thumbnail
        if ($course->thumbnail_path && Storage::disk('public')->exists($course->thumbnail_path)) {
            Storage::disk('public')->delete($course->thumbnail_path);
        }

        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course deleted.');
    }

    public function builder(Course $course)
    {
        // builder butuh kategori untuk form Course di panel kanan
        $categories = Category::orderBy('name')->get();

        return view('admin.pages.courses.builder', compact('course', 'categories'));
    }

    /**
     * Endpoint khusus toggle publish/draft (opsional tapi rapi).
     */
    public function toggleStatus(Course $course)
    {
        $next = $course->status === 'published' ? 'draft' : 'published';
        $course->update(['status' => $next]);

        return back()->with('success', "Course status set to {$next}.");
    }
}
