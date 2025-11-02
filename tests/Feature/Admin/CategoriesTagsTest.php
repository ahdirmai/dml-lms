<?php

use App\Models\User;
use App\Models\Lms\Category;
use App\Models\Lms\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->admin = User::factory()->create(['active_role' => 'admin']);
    $this->admin->assignRole(['admin']);
});

/**
 * Categories
 */
it('lists categories', function () {
    Category::factory()->count(2)->create();
    $this->actingAs($this->admin)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('Categories');
});

it('creates category', function () {
    $resp = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
        'name' => 'Programming',
        'slug' => 'programming',
        'description' => 'Desc',
    ]);
    $resp->assertRedirect(route('admin.categories.index'));
    expect(Category::where('slug', 'programming')->exists())->toBeTrue();
});

it('updates category', function () {
    $cat = Category::factory()->create(['name' => 'Old', 'slug' => 'old']);
    $resp = $this->actingAs($this->admin)->put(route('admin.categories.update', $cat), [
        'name' => 'New Name',
        'slug' => 'new-slug',
        'description' => 'New desc',
    ]);
    $resp->assertRedirect(route('admin.categories.index'));
    $cat->refresh();
    expect($cat->name)->toBe('New Name');
    expect($cat->slug)->toBe('new-slug');
});

it('deletes category', function () {
    $cat = Category::factory()->create();
    $resp = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $cat));
    $resp->assertRedirect();
    expect(Category::where('id', $cat->id)->exists())->toBeFalse();
});

/**
 * Tags
 */
it('lists tags', function () {
    Tag::factory()->count(2)->create();
    $this->actingAs($this->admin)
        ->get(route('admin.tags.index'))
        ->assertOk()
        ->assertSee('Tags');
});

it('creates tag', function () {
    $resp = $this->actingAs($this->admin)->post(route('admin.tags.store'), [
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
    $resp->assertRedirect(route('admin.tags.index'));
    expect(Tag::where('slug', 'laravel')->exists())->toBeTrue();
});

it('updates tag', function () {
    $tag = Tag::factory()->create(['name' => 'Old', 'slug' => 'old']);
    $resp = $this->actingAs($this->admin)->put(route('admin.tags.update', $tag), [
        'name' => 'New',
        'slug' => 'new',
    ]);
    $resp->assertRedirect(route('admin.tags.index'));
    $tag->refresh();
    expect($tag->name)->toBe('New');
    expect($tag->slug)->toBe('new');
});

it('deletes tag', function () {
    $tag = Tag::factory()->create();
    $resp = $this->actingAs($this->admin)->delete(route('admin.tags.destroy', $tag));
    $resp->assertRedirect();
    expect(Tag::where('id', $tag->id)->exists())->toBeFalse();
});
