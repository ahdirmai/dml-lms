<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate(['role' => 'required|string']);
        $user = Auth::user();
        // return $user->hasRoles('role');

        // Role yang diminta
        $role = $request->role;

        // Cek apakah user punya role itu
        if (! $user->hasRole($role)) {
            return back()->with('error', 'Kamu tidak memiliki role tersebut.');
        }

        // Jalankan fungsi switch role (punyamu)
        $user->switchRole($role);

        // Tentukan redirect ke dashboard sesuai role baru
        $redirect = match ($role) {
            'admin'      => route('admin.dashboard'),
            'instructor' => route('instructor.dashboard'),
            'student'    => route('user.dashboard'),
            default      => route('dashboard'), // fallback
        };

        return redirect($redirect)->with(
            'success',
            'Role aktif diganti ke: ' . ucfirst($role)
        );
    }
}
