<?php

// app/Listeners/SetActiveRoleOnLogin.php
namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class SetActiveRoleOnLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Jika belum ada active_role atau active_role tidak lagi dimiliki user
        $roleNames = $user->getRoleNames();
        if ($roleNames->isEmpty()) {
            return;
        }

        if (! $user->active_role || ! $roleNames->contains($user->active_role)) {
            // Prioritaskan admin jika punya; jika tidak, ambil role pertama
            $newActive = $roleNames->contains('admin') ? 'admin' : $roleNames->first();
            $user->forceFill(['active_role' => $newActive])->save();
        }
    }
}
