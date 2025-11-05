<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Lms\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->get('q', '');
        $tags = Tag::query()
            ->when($q, fn($qr) => $qr->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(15)->withQueryString();

        return view('admin.pages.tags.index', compact('tags', 'q'));
    }

    public function create()
    {
        return view('admin.pages.tags.create');
    }

    public function store(StoreTagRequest $request)
    {
        Tag::create($request->validated());
        return redirect()->route('admin.tags.index')->with('success', 'Tag created.');
    }

    public function edit(Tag $tag)
    {
        return view('admin.pages.tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());
        return redirect()->route('admin.tags.index')->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag)
    {
        try {
            $tag->delete(); // pivot course_tag CASCADE
            return back()->with('success', 'Tag deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Delete failed.');
        }
    }
}
