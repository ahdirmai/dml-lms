<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreCategoryRequest;
use App\Http\Requests\Instructor\UpdateCategoryRequest;
use App\Models\Lms\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->get('q', '');
        $categories = Category::query()
            ->when(
                $q,
                fn($qr) =>
                $qr->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
            )
            ->where('created_by', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('instructor.pages.categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        return view('instructor.pages.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            DB::beginTransaction();

            $payload = $request->validated();
            $payload['created_by'] = Auth::id();

            Category::create($payload);

            DB::commit();

            return redirect()
                ->route('instructor.categories.index')
                ->with('success', 'Category created.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    public function edit(Category $category)
    {
        abort_unless($category->created_by === Auth::id(), 403);
        return view('instructor.pages.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        abort_unless($category->created_by === Auth::id(), 403);

        try {
            DB::beginTransaction();

            $category->update($request->validated());

            DB::commit();

            return redirect()
                ->route('instructor.categories.index')
                ->with('success', 'Category updated.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        abort_unless($category->created_by === Auth::id(), 403);

        try {
            DB::beginTransaction();

            $name = $category->name;

            // RESTRICT di DB/pivot akan melempar exception jika masih dipakai
            $category->delete();

            DB::commit();

            return back()->with('success', "Category \"{$name}\" deleted.");
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Cannot delete: category is in use or another error occurred.');
        }
    }
}
