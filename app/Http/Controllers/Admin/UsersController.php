<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Throwable;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $q    = $request->string('q')->toString();
        $role = $request->string('role')->toString();

        $users = User::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($role, function ($query) use ($role) {
                $query->whereHas('roles', fn($qr) => $qr->where('name', $role));
            })
            ->with('roles:id,name')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::query()->orderBy('name')->pluck('name');

        return view('admin.pages.users.index', compact('users', 'roles', 'q', 'role'));
    }

    public function create()
    {
        $roles = Role::query()->orderBy('name')->pluck('name');
        return view('admin.pages.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles'    => ['nullable', 'array'],
            'roles.*'  => ['string', Rule::exists('roles', 'name')],
        ]);

        try {
            DB::beginTransaction();

            $user = new User();
            $user->name  = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->email_verified_at = now();
            $user->save();

            // Default role: student
            $assignRoles = collect($data['roles'] ?? [])->filter()->values()->all();
            if (! in_array('student', $assignRoles, true)) {
                $assignRoles[] = 'student';
            }
            $user->syncRoles($assignRoles);

            // Set active_role bila kosong
            if (empty($user->active_role)) {
                $user->active_role = $assignRoles[0] ?? 'student';
                $user->save();
            }

            DB::commit();

            return redirect()->route('admin.users.index')->with('success', 'User created.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->pluck('name');
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('admin.pages.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'    => ['nullable', 'string', 'min:8'],
            'roles'       => ['nullable', 'array'],
            'roles.*'     => ['string', Rule::exists('roles', 'name')],
            'active_role' => ['nullable', Rule::in($request->input('roles', []))],
        ]);

        try {
            DB::beginTransaction();

            // Kunci baris user untuk konsistensi
            $fresh = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $fresh->name  = $data['name'];
            $fresh->email = $data['email'];
            if (!empty($data['password'])) {
                $fresh->password = Hash::make($data['password']);
            }
            $fresh->save();

            // Role sync (pastikan 'student' selalu ada)
            $assignRoles = collect($data['roles'] ?? [])->filter()->values()->all();
            if (! in_array('student', $assignRoles, true)) {
                $assignRoles[] = 'student';
            }
            $fresh->syncRoles($assignRoles);

            // Pastikan active_role tetap valid
            if ($request->filled('active_role')) {
                $ar = $request->string('active_role');
                $selectedRoles = collect($request->input('roles', []))->filter()->values();
                if ($selectedRoles->contains($ar)) {
                    $fresh->active_role = $ar;
                } else {
                    $fresh->active_role = null; // fallback jika tidak konsisten
                }
                $fresh->save();
            } else {
                // Jika tidak dikirim, tetapi active_role lama tidak ada di roles baru, fallback
                if ($fresh->active_role && ! in_array($fresh->active_role, $assignRoles, true)) {
                    $fresh->active_role = $assignRoles[0] ?? 'student';
                    $fresh->save();
                }
            }

            DB::commit();

            return redirect()->route('admin.users.index')->with('success', 'User updated.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        // Hindari hapus diri sendiri (opsional)
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        try {
            DB::beginTransaction();

            // Kunci baris user
            $fresh = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Optional: detach roles dulu agar bersih (Spatie biasanya handle, tapi ini rapi)
            // $fresh->roles()->detach();

            $fresh->delete();

            DB::commit();

            return redirect()->route('admin.users.index')->with('success', 'User deleted.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
