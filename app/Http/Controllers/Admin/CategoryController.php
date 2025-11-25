<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Lms\Category;
use Illuminate\Http\Request;
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
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        return view('admin.pages.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            DB::beginTransaction();

            Category::create($request->validated());

            DB::commit();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Category created successfully.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    public function edit(Category $category)
    {
        return view('admin.pages.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            DB::beginTransaction();

            $category->update($request->validated());

            DB::commit();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        try {
            DB::beginTransaction();

            $name = $category->name;

            // RESTRICT di DB akan memicu exception jika category sedang digunakan di pivot course
            $category->delete();

            DB::commit();

            return back()->with('success', "Category \"{$name}\" deleted successfully.");
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with(
                'error',
                'Cannot delete category because it is still in use or another error occurred: ' . $e->getMessage()
            );
        }
    }
}
