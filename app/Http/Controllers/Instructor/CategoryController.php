<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreCategoryRequest;
use App\Http\Requests\Instructor\UpdateCategoryRequest;
use App\Models\Lms\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->get('q', '');
        $categories = Category::query()
            ->when($q, fn($qr) => $qr->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
            ->orderBy('created_at', 'desc')
            ->where('created_by', Auth::user()->id)
            ->paginate(15)->withQueryString();

        return view('instructor.pages.categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        return view('instructor.pages.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());
        return redirect()->route('instructor.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('instructor.pages.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        return redirect()->route('instructor.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        // RESTRICT di level DB untuk course; akan gagal jika masih dipakai
        try {
            $category->delete();
            return back()->with('success', 'Category deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Cannot delete: category is in use.');
        }
    }
}
