<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Throwable;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $roles = Role::query()
            ->when($q, fn($qr) => $qr->where('name', 'like', "%{$q}%"))
            ->with(['permissions:id,name'])
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.pages.roles.index', compact('roles', 'q'));
    }

    public function create()
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name');

        return view('admin.pages.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions'     => ['nullable', 'array'],
            'permissions.*'   => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name'       => $data['name'],
                'guard_name' => 'web',
            ]);

            // Sinkronisasi permission
            $role->syncPermissions($data['permissions'] ?? []);

            DB::commit();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Role created.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function edit(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name');

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.pages.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions'     => ['nullable', 'array'],
            'permissions.*'   => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);

        try {
            DB::beginTransaction();

            // Kunci baris role untuk mencegah race condition (opsional)
            $fresh = Role::query()
                ->whereKey($role->id)
                ->lockForUpdate()
                ->firstOrFail();

            $fresh->name = $data['name'];
            $fresh->save();

            $fresh->syncPermissions($data['permissions'] ?? []);

            DB::commit();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Role updated.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        // Lindungi role inti
        if ($role->name === 'superadmin') {
            return back()->with('error', 'Superadmin cannot be deleted.');
        }

        try {
            DB::beginTransaction();

            // Kunci baris role
            $fresh = Role::query()
                ->whereKey($role->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Cek apakah role masih terpasang pada user
            $attachedUsers = method_exists($fresh, 'users') ? $fresh->users()->exists() : false;
            if ($attachedUsers) {
                DB::rollBack();
                return back()->with('error', 'Cannot delete: role is still assigned to one or more users.');
            }

            // Optional: detach permissions dulu (tidak wajib, tapi rapi)
            $fresh->permissions()->detach();

            $name = $fresh->name;
            $fresh->delete();

            DB::commit();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', "Role \"{$name}\" deleted.");
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }
}
