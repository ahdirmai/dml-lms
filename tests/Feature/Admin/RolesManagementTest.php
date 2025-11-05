<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->admin = User::factory()->create(['active_role' => 'admin']);
    $this->admin->assignRole(['admin']);
});

it('lists roles', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('Role Management');
});

it('creates role with permissions', function () {
    $perm = Permission::firstOrCreate(['name' => 'reports.view']);

    $resp = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
        'name' => 'auditor',
        'guard_name' => 'web',
        'permissions' => ['reports.view'],
    ]);

    $resp->assertRedirect(route('admin.roles.index'));
    $role = Role::where('name', 'auditor')->first();
    expect($role)->not->toBeNull();
    expect($role->hasPermissionTo('reports.view'))->toBeTrue();
});

it('updates role and sync permissions', function () {
    $role = Role::firstOrCreate(['name' => 'temp', 'guard_name' => 'web']);
    $p1 = Permission::firstOrCreate(['name' => 'alpha']);
    $p2 = Permission::firstOrCreate(['name' => 'beta']);
    $role->syncPermissions(['alpha']);

    $resp = $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
        'name' => 'temp',
        'guard_name' => 'web',
        'permissions' => ['beta'],
    ]);

    $resp->assertRedirect(route('admin.roles.index'));
    $role->refresh();
    expect($role->hasPermissionTo('alpha'))->toBeFalse();
    expect($role->hasPermissionTo('beta'))->toBeTrue();
});

it('deletes role', function () {
    $role = Role::firstOrCreate(['name' => 'to-delete', 'guard_name' => 'web']);

    $resp = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));
    $resp->assertRedirect();

    expect(Role::where('name', 'to-delete')->exists())->toBeFalse();
});
