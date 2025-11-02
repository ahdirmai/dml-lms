<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->admin = User::factory()->create(['active_role' => 'admin']);
    $this->admin->assignRole(['admin']);
});

it('lists permissions', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.permissions.index'))
        ->assertOk()
        ->assertSee('Permission Management');
});

it('creates permission', function () {
    $resp = $this->actingAs($this->admin)->post(route('admin.permissions.store'), [
        'name' => 'new.permission',
        'guard_name' => 'web',
    ]);

    $resp->assertRedirect(route('admin.permissions.index'));
    expect(Permission::where('name', 'new.permission')->exists())->toBeTrue();
});

it('updates permission', function () {
    $p = Permission::firstOrCreate(['name' => 'old.permission', 'guard_name' => 'web']);

    $resp = $this->actingAs($this->admin)->put(route('admin.permissions.update', $p), [
        'name' => 'renamed.permission',
        'guard_name' => 'web',
    ]);

    $resp->assertRedirect(route('admin.permissions.index'));
    expect(Permission::where('name', 'old.permission')->exists())->toBeFalse();
    expect(Permission::where('name', 'renamed.permission')->exists())->toBeTrue();
});

it('deletes permission', function () {
    $p = Permission::firstOrCreate(['name' => 'to.delete', 'guard_name' => 'web']);

    $resp = $this->actingAs($this->admin)->delete(route('admin.permissions.destroy', $p));
    $resp->assertRedirect();

    expect(Permission::where('name', 'to.delete')->exists())->toBeFalse();
});
