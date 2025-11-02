<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\PermissionsController;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/switch-role', [RoleSwitchController::class, 'switch'])
    ->middleware('auth')
    ->name('switch.role');

// routes/web.php


// admin
Route::middleware(['auth', 'role.active:admin'])->group(function () {
    Route::resource('admin/users', UsersController::class)->names([
        'index'   => 'admin.users.index',
        'create'  => 'admin.users.create',
        'store'   => 'admin.users.store',
        'edit'    => 'admin.users.edit',
        'update'  => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);
});


Route::middleware(['auth', 'role.active:admin'])->group(function () {
    // Roles
    Route::resource('admin/roles', RolesController::class)->names([
        'index'   => 'admin.roles.index',
        'create'  => 'admin.roles.create',
        'store'   => 'admin.roles.store',
        'edit'    => 'admin.roles.edit',
        'update'  => 'admin.roles.update',
        'destroy' => 'admin.roles.destroy',
        'show'    => 'admin.roles.show',
    ])->except(['show']); // biasanya show tidak diperlukan

    // Permissions
    Route::resource('admin/permissions', PermissionsController::class)->names([
        'index'   => 'admin.permissions.index',
        'create'  => 'admin.permissions.create',
        'store'   => 'admin.permissions.store',
        'edit'    => 'admin.permissions.edit',
        'update'  => 'admin.permissions.update',
        'destroy' => 'admin.permissions.destroy',
        'show'    => 'admin.permissions.show',
    ])->except(['show']);
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin')->name('admin.')->middleware(['role.active:admin'])->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('tags', TagController::class)->except(['show']);
    });
});




require __DIR__ . '/auth.php';
