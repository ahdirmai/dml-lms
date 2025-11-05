<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

function actingAsAdmin(): User
{
    $admin = User::factory()->create(['active_role' => 'admin']);
    $admin->assignRole(['admin']);
    return $admin;
}

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('lists users', function () {
    $admin = actingAsAdmin();
    User::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Users Management');
});

it('creates a user with roles and active role', function () {
    $admin = actingAsAdmin();

    $payload = [
        'name' => 'User X',
        'email' => 'userx@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => ['student', 'instructor'],
        'active_role' => 'student',
    ];

    $resp = $this->actingAs($admin)->post(route('admin.users.store'), $payload);

    $resp->assertRedirect(route('admin.users.index'));
    $this->assertDatabaseHas('users', ['email' => 'userx@example.com']);

    $created = User::where('email', 'userx@example.com')->first();
    expect($created->hasRole('student'))->toBeTrue();
    expect($created->hasRole('instructor'))->toBeTrue();
    expect($created->active_role)->toBe('student');
});

it('updates a user, sync roles and active_role', function () {
    $admin = actingAsAdmin();
    $u = User::factory()->create(['name' => 'Old', 'email' => 'old@example.com']);
    $u->assignRole('student');

    $payload = [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'roles' => ['admin'],
        'active_role' => 'admin',
        'password' => '',
        'password_confirmation' => '',
    ];

    $resp = $this->actingAs($admin)->put(route('admin.users.update', $u), $payload);
    $resp->assertRedirect(route('admin.users.index'));

    $u->refresh();
    expect($u->name)->toBe('New Name');
    expect($u->email)->toBe('new@example.com');
    expect($u->hasRole('admin'))->toBeTrue();
    expect($u->active_role)->toBe('admin');
});

it('deletes a user', function () {
    $admin = actingAsAdmin();
    $u = User::factory()->create();

    $resp = $this->actingAs($admin)->delete(route('admin.users.destroy', $u));
    $resp->assertRedirect();

    $this->assertDatabaseMissing('users', ['id' => $u->id]);
});
