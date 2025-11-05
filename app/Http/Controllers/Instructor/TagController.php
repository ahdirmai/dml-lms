<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Lms\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->get('q', '');

        $tags = Tag::query()
            ->when(
                $q,
                fn($qr) =>
                $qr->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
            )
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('instructor.pages.tags.index', compact('tags', 'q'));
    }

    public function create()
    {
        return view('instructor.pages.tags.create');
    }

    public function store(StoreTagRequest $request)
    {
        try {
            DB::beginTransaction();

            Tag::create($request->validated());

            DB::commit();

            return redirect()
                ->route('instructor.tags.index')
                ->with('success', 'Tag created.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create tag: ' . $e->getMessage());
        }
    }

    public function edit(Tag $tag)
    {
        return view('instructor.pages.tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        try {
            DB::beginTransaction();

            $tag->update($request->validated());

            DB::commit();

            return redirect()
                ->route('instructor.tags.index')
                ->with('success', 'Tag updated.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update tag: ' . $e->getMessage());
        }
    }

    public function destroy(Tag $tag)
    {
        try {
            DB::beginTransaction();

            $name = $tag->name;

            // Jika pivot course_tag sudah FK CASCADE, cukup delete().
            // Jika belum, detach dulu:
            // $tag->courses()->detach();

            $tag->delete();

            DB::commit();

            return back()->with('success', "Tag \"{$name}\" deleted.");
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to delete tag: ' . $e->getMessage());
        }
    }
}
