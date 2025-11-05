<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('denies non-admin active_role to admin routes', function () {
    $user = User::factory()->create(['active_role' => 'student']);
    $user->assignRole(['student']);

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

it('allows admin active_role', function () {
    $user = User::factory()->create(['active_role' => 'admin']);
    $user->assignRole(['admin']);

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertOk();
});
