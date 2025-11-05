<?php

use Illuminate\Support\Facades\Route;

// ---------- Controllers (Public & Auth) ----------
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleSwitchController;

// ---------- Controllers (Admin: Core Management) ----------
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;

// ---------- Controllers (Admin: LMS) ----------
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\CourseAssignController;
use App\Http\Controllers\Admin\CourseProgressController;
use App\Http\Controllers\Instructor\CourseAssignController as InstructorCourseAssignController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\CourseProgressController as InstructorCourseProgressController;
use App\Http\Controllers\Instructor\LessonController as InstructorLessonController;
use App\Http\Controllers\Instructor\ModuleController as InstructorModuleController;
use App\Http\Controllers\Instructor\QuizController as InstructorQuizController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'));

Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated User Profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Switch role (for multi-role users)
    Route::post('/switch-role', [RoleSwitchController::class, 'switch'])->name('switch.role');
});

/*
|--------------------------------------------------------------------------
| Admin Area
| - All admin routes live under /admin
| - Name prefix: admin.*
| - Middleware: auth + role.active:admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role.active:admin'])
    ->group(function () {

        /*
        |------------------------------
        | Admin: User & RBAC Management
        |------------------------------
        */
        Route::resource('users', UsersController::class)->names([
            'index'   => 'users.index',
            'create'  => 'users.create',
            'store'   => 'users.store',
            'edit'    => 'users.edit',
            'update'  => 'users.update',
            'destroy' => 'users.destroy',
        ])->except(['show']);

        Route::resource('roles', RolesController::class)->names([
            'index'   => 'roles.index',
            'create'  => 'roles.create',
            'store'   => 'roles.store',
            'edit'    => 'roles.edit',
            'update'  => 'roles.update',
            'destroy' => 'roles.destroy',
        ])->except(['show']);

        Route::resource('permissions', PermissionsController::class)->names([
            'index'   => 'permissions.index',
            'create'  => 'permissions.create',
            'store'   => 'permissions.store',
            'edit'    => 'permissions.edit',
            'update'  => 'permissions.update',
            'destroy' => 'permissions.destroy',
        ])->except(['show']);

        /*
        |------------------------------
        | Admin: Taxonomy (Categories/Tags)
        |------------------------------
        */
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('tags',       TagController::class)->except(['show']);

        /*
        |------------------------------
        | Admin: LMS — Courses & Builder
        |------------------------------
        */
        // Courses
        Route::get('courses',                 [CourseController::class, 'index'])->name('courses.index');
        Route::get('courses/create',          [CourseController::class, 'create'])->name('courses.create');
        Route::post('courses',                [CourseController::class, 'store'])->name('courses.store');
        Route::get('courses/{course}/edit',   [CourseController::class, 'edit'])->name('courses.edit');
        Route::patch('courses/{course}',      [CourseController::class, 'update'])->name('courses.update');
        Route::post('courses/{course}/publish', [CourseController::class, 'publish'])->name('courses.publish');
        Route::delete('courses/{course}',     [CourseController::class, 'destroy'])->name('courses.destroy');

        // Modules
        Route::post('courses/{course}/modules',        [ModuleController::class, 'store'])->name('modules.store');
        Route::patch('modules/{module}',               [ModuleController::class, 'update'])->name('modules.update');
        Route::delete('modules/{module}',              [ModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('courses/{course}/modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');

        // Lessons
        Route::post('modules/{module}/lessons',        [LessonController::class, 'store'])->name('lessons.store');
        Route::patch('lessons/{lesson}',               [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('lessons/{lesson}',              [LessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('modules/{module}/lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');

        // Quizzes
        Route::post('lessons/{lesson}/quiz',           [QuizController::class, 'upsert'])->name('quizzes.upsert');
        Route::post('quizzes/{quiz}/questions',        [QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
        Route::patch('questions/{question}',           [QuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
        Route::delete('questions/{question}',          [QuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');

        // Enrollment Assign (Instructor/Admin assigns students)
        Route::get('courses/{course}/assign-students', [CourseAssignController::class, 'form'])->name('courses.assign');
        Route::post('courses/{course}/assign-students', [CourseAssignController::class, 'store'])->name('courses.assign.store');
        Route::delete('courses/{course}/students/{user}', [CourseAssignController::class, 'remove'])->name('courses.assign.remove');

        // Progress Tracking
        Route::get('courses/{course}/progress', [CourseProgressController::class, 'show'])->name('courses.progress');
    });

Route::prefix('instructor')
    ->name('instructor.')
    ->middleware(['auth', 'role.active:instructor'])
    ->group(function () {

        /*
        |------------------------------
        | Instructor: Taxonomy (Categories/Tags)
        |------------------------------
        */
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('tags',       TagController::class)->except(['show']);

        /*
        |------------------------------
        | Instructor: LMS — Courses & Builder
        |------------------------------
        */
        // Courses
        Route::get('courses',                 [InstructorCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/create',          [InstructorCourseController::class, 'create'])->name('courses.create');
        Route::post('courses',                [InstructorCourseController::class, 'store'])->name('courses.store');
        Route::get('courses/{course}/edit',   [InstructorCourseController::class, 'edit'])->name('courses.edit');
        Route::patch('courses/{course}',      [InstructorCourseController::class, 'update'])->name('courses.update');
        Route::post('courses/{course}/publish', [InstructorCourseController::class, 'publish'])->name('courses.publish');
        Route::delete('courses/{course}',     [InstructorCourseController::class, 'destroy'])->name('courses.destroy');

        // Modules
        Route::post('courses/{course}/modules',        [InstructorModuleController::class, 'store'])->name('modules.store');
        Route::patch('modules/{module}',               [InstructorModuleController::class, 'update'])->name('modules.update');
        Route::delete('modules/{module}',              [InstructorModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('courses/{course}/modules/reorder', [InstructorModuleController::class, 'reorder'])->name('modules.reorder');

        // Lessons
        Route::post('modules/{module}/lessons',        [InstructorLessonController::class, 'store'])->name('lessons.store');
        Route::patch('lessons/{lesson}',               [InstructorLessonController::class, 'update'])->name('lessons.update');
        Route::delete('lessons/{lesson}',              [InstructorLessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('modules/{module}/lessons/reorder', [InstructorLessonController::class, 'reorder'])->name('lessons.reorder');

        // Quizzes
        Route::post('lessons/{lesson}/quiz',           [InstructorQuizController::class, 'upsert'])->name('quizzes.upsert');
        Route::post('quizzes/{quiz}/questions',        [InstructorQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
        Route::patch('questions/{question}',           [InstructorQuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
        Route::delete('questions/{question}',          [InstructorQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');

        // Enrollment Assign (Instructor/Admin assigns students)
        Route::get('courses/{course}/assign-students', [InstructorCourseAssignController::class, 'form'])->name('courses.assign');
        Route::post('courses/{course}/assign-students', [InstructorCourseAssignController::class, 'store'])->name('courses.assign.store');
        Route::delete('courses/{course}/students/{user}', [InstructorCourseAssignController::class, 'remove'])->name('courses.assign.remove');

        // Progress Tracking
        Route::get('courses/{course}/progress', [InstructorCourseProgressController::class, 'show'])->name('courses.progress');
    });


use Illuminate\Support\Facades\Artisan;

Route::get('/_util/cache', function () {

    // return [request('key'), env('MAINT_KEY')];
    abort_unless(request('key') === env('MAINT_KEY'), 403);
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('view:cache');
    Artisan::call('event:cache');
    return 'OK';
});

require __DIR__ . '/auth.php';
