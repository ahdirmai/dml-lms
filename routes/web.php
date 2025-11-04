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

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuizController;

// Pastikan Anda telah mengimpor semua Controller di atas file ini

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // Courses Index
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');

    // Course Builder Routes
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');

    // Route Edit/Builder (GET)
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');

    // Course Updates & Actions
    // POST dengan method spoofing di Blade akan mengarah ke update
    Route::patch('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    // Hapus showData karena non-AJAX
    Route::post('/courses/{course}/publish', [CourseController::class, 'publish'])->name('courses.publish');
    // POST dengan method spoofing di Blade akan mengarah ke destroy
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // Modules (Via Form POST Biasa)
    Route::post('/courses/{course}/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::patch('/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
    Route::post('/courses/{course}/modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');

    // Lessons (Via Form POST Biasa)
    Route::post('/modules/{module}/lessons', [LessonController::class, 'store'])->name('lessons.store');

    Route::patch('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
    Route::post('/modules/{module}/lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');

    // Quizzes (Via Form POST Biasa)
    // Asumsi QuizController juga diubah untuk non-AJAX (redirect)
    Route::post('/lessons/{lesson}/quiz', [QuizController::class, 'upsert'])->name('quizzes.upsert');
    Route::post('/quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::patch('/questions/{question}', [QuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
    Route::delete('/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
});
require __DIR__ . '/auth.php';
