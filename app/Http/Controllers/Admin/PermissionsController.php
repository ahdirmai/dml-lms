<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Throwable;

class PermissionsController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $permissions = Permission::query()
            ->when($q, fn($qr) => $qr->where('name', 'like', "%{$q}%"))
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.permissions.index', compact('permissions', 'q'));
    }

    public function create()
    {
        return view('admin.pages.permissions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:permissions,name'],
        ]);

        try {
            DB::beginTransaction();

            Permission::create([
                'name'       => $data['name'],
                'guard_name' => 'web',
            ]);

            DB::commit();

            return redirect()
                ->route('admin.permissions.index')
                ->with('success', 'Permission created.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create permission: ' . $e->getMessage());
        }
    }

    public function edit(Permission $permission)
    {
        abort_unless($permission->guard_name === 'web', 404);

        return view('admin.pages.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        abort_unless($permission->guard_name === 'web', 404);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('permissions', 'name')->ignore($permission->id),
            ],
        ]);

        try {
            DB::beginTransaction();

            $permission->name = $data['name'];
            $permission->save();

            DB::commit();

            return redirect()
                ->route('admin.permissions.index')
                ->with('success', 'Permission updated.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update permission: ' . $e->getMessage());
        }
    }

    public function destroy(Permission $permission)
    {
        abort_unless($permission->guard_name === 'web', 404);

        try {
            DB::beginTransaction();

            // Cegah penghapusan jika masih terpasang pada role atau user
            $rolesCount = $permission->roles()->count();
            $usersCount = method_exists($permission, 'users') ? $permission->users()->count() : 0;

            if ($rolesCount > 0 || $usersCount > 0) {
                DB::rollBack();
                return redirect()
                    ->route('admin.permissions.index')
                    ->with('error', "Cannot delete: permission is attached to {$rolesCount} role(s) and {$usersCount} user(s).");
            }

            $permission->delete();

            DB::commit();

            return redirect()
                ->route('admin.permissions.index')
                ->with('success', 'Permission deleted.');
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('admin.permissions.index')
                ->with('error', 'Failed to delete permission: ' . $e->getMessage());
        }
    }
}
