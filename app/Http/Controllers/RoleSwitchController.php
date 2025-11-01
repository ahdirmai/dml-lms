<?php

// app/Http/Controllers/RoleSwitchController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate(['role' => 'required|string']);
        $user = $request->user();

        if ($user->switchRole($request->string('role'))) {
            return back()->with('success', 'Role aktif diganti ke: ' . $request->role);
        }
        return back()->with('error', 'Kamu tidak memiliki role tersebut.');
    }
}
