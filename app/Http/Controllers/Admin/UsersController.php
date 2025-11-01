<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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

        return redirect()->route('admin.users.index')->with('success', 'User created.');
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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'roles'    => ['nullable', 'array'],
            'roles.*'  => ['string', Rule::exists('roles', 'name')],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        // Role sync (tetap pastikan student ada)
        $assignRoles = collect($data['roles'] ?? [])->filter()->values()->all();
        if (! in_array('student', $assignRoles, true)) {
            $assignRoles[] = 'student';
        }
        $user->syncRoles($assignRoles);

        // Pastikan active_role tetap valid
        if ($user->active_role && ! in_array($user->active_role, $assignRoles, true)) {
            $user->active_role = $assignRoles[0] ?? 'student';
            $user->save();
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        // Hindari hapus diri sendiri (opsional)
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
