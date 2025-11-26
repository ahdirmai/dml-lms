<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseAssignController as AdminCourseAssignController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
// Public Controllers
use App\Http\Controllers\Admin\CourseProgressController as AdminCourseProgressController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
// Admin Controllers
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserIntegrationController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Auth\SsoLoginController;
use App\Http\Controllers\Instructor\CategoryController as InstructorCategoryController;
use App\Http\Controllers\Instructor\CourseAssignController as InstructorCourseAssignController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\CourseProgressController as InstructorCourseProgressController;
use App\Http\Controllers\Instructor\LessonController as InstructorLessonController;
// Instructor Controllers
use App\Http\Controllers\Instructor\ModuleController as InstructorModuleController;
use App\Http\Controllers\Instructor\QuizController as InstructorQuizController;
use App\Http\Controllers\Instructor\TagController as InstructorTagController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\User\CourseController as UserCourseController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\LessonController as UserLessonController;
// User Controllers
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $user = Auth::user();

    if (! $user) {
        return redirect()->route('login');
    }

    $activeRole = $user->active_role ?? $user->getRoleNames()->first();

    return match ($activeRole) {
        'admin' => redirect()->route('admin.dashboard'),
        'instructor' => redirect()->route('instructor.dashboard'),
        'student' => redirect()->route('user.dashboard'),
        default => redirect()->route('dashboard'),
    };
});

Route::match(['GET', 'POST'], '/sso/login', SsoLoginController::class)
    ->name('sso.login')
    ->middleware('web');

/*
|--------------------------------------------------------------------------
| User (Student)
|--------------------------------------------------------------------------
*/
Route::name('user.')
    ->middleware(['auth', 'role.active:student'])
    ->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/my-courses', [UserCourseController::class, 'index'])->name('courses.index');

        Route::get('/courses/{course}', [UserCourseController::class, 'show'])->name('courses.show');
        Route::get('/lessons/{lesson}', [UserLessonController::class, 'show'])->name('lessons.show');
        Route::post('/lessons/{lesson}/progress', [UserLessonController::class, 'updateProgress'])->name('lessons.progress');
        Route::post('/lessons/{lesson}/complete', [UserLessonController::class, 'markAsComplete'])->name('lessons.complete');

        Route::post('/courses/{course}/test/{type}', [UserCourseController::class, 'submitTest'])
            ->name('courses.test.submit');

        /**
         * Endpoint untuk menangani submit review (bintang).
         */
        Route::post('/courses/{course}/review', [UserCourseController::class, 'submitReview'])
            ->name('courses.review.submit');

        Route::get('/courses/{course}/certificate', [UserCourseController::class, 'certificate'])
            ->name('courses.certificate');
    });

/*
|--------------------------------------------------------------------------
| Authenticated User Profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/switch-role', [RoleSwitchController::class, 'switch'])->name('switch.role');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role.active:admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

        // RBAC
        Route::resource('users', UsersController::class)->except(['show']);
        Route::get('user-activity', [\App\Http\Controllers\Admin\UserActivityController::class, 'index'])->name('user-activity.index');

        Route::prefix('integrations')->name('integration.')->group(function () {

            // Halaman utama integrasi user (Blade view)
            // GET /admin/integrations/users
            Route::get('users', [UserIntegrationController::class, 'index'])
                ->name('users.index');

            // Preview data dari sistem internal (AJAX, JSON)
            // POST /admin/integrations/users/preview
            Route::post('users/preview', [UserIntegrationController::class, 'preview'])
                ->name('users.preview');

            // Import user yang dipilih (AJAX/normal POST)
            // POST /admin/integrations/users/import
            Route::post('users/import', [UserIntegrationController::class, 'import'])
                ->name('users.import');
        });
        Route::resource('roles', RolesController::class)->except(['show']);
        Route::resource('permissions', PermissionsController::class)->except(['show']);

        // Taxonomy
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('tags', TagController::class)->except(['show']);

        // Courses
        Route::get('courses', [AdminCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/create', [AdminCourseController::class, 'create'])->name('courses.create');
        Route::post('courses', [AdminCourseController::class, 'store'])->name('courses.store');
        Route::get('courses/{course}/edit', [AdminCourseController::class, 'edit'])->name('courses.edit');
        Route::patch('courses/{course}', [AdminCourseController::class, 'update'])->name('courses.update');
        Route::patch('courses/{course}/status', [AdminCourseController::class, 'updateStatus'])->name('courses.status.update');
        Route::post('courses/{course}/publish', [AdminCourseController::class, 'publish'])->name('courses.publish');
        Route::delete('courses/{course}', [AdminCourseController::class, 'destroy'])->name('courses.destroy');

        // Modules
        Route::post('courses/{course}/modules', [AdminModuleController::class, 'store'])->name('modules.store');
        Route::patch('modules/{module}', [AdminModuleController::class, 'update'])->name('modules.update');
        Route::delete('modules/{module}', [AdminModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('courses/{course}/modules/reorder', [AdminModuleController::class, 'reorder'])->name('modules.reorder');

        // Lessons
        Route::post('modules/{module}/lessons', [AdminLessonController::class, 'store'])->name('lessons.store');
        Route::patch('lessons/{lesson}', [AdminLessonController::class, 'update'])->name('lessons.update');
        Route::delete('lessons/{lesson}', [AdminLessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('modules/{module}/lessons/reorder', [AdminLessonController::class, 'reorder'])->name('lessons.reorder');

        // === Pre/Posttest Store (views-only stage; controller bisa diisi nanti) ===
        Route::post('courses/{course}/pretest', [AdminQuizController::class, 'storePretest'])->name('courses.pretest.store');
        Route::post('courses/{course}/posttest', [AdminQuizController::class, 'storePosttest'])->name('courses.posttest.store');
        // Quizzes

        Route::prefix('quizzes/{quiz}')->group(function () {
            Route::post('questions', [AdminQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
            Route::put('questions/{question}', [AdminQuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
            Route::delete('questions/{question}', [AdminQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
            Route::post('questions/reorder', [AdminQuizController::class, 'reorderQuestions'])->name('quizzes.questions.reorder'); // optional

        });

        Route::post('courses/{course}/posttest/copy-from-pretest', [AdminQuizController::class, 'syncFromPretest'])
            ->name('courses.posttest.copyFromPretest');

        Route::post(
            'courses/{course}/quizzes/{kind}/import', // <--- SALAH
            [AdminQuizController::class, 'importByKind']
        )
            ->name('courses.quizzes.import');
        // Route::post('lessons/{lesson}/quiz', [AdminQuizController::class, 'upsert'])->name('quizzes.upsert');
        // Route::post('quizzes/{quiz}/questions', [AdminQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
        // Route::patch('questions/{question}', [AdminQuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
        // Route::delete('questions/{question}', [AdminQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');

        // Assignments
        Route::get('courses/{course}/assign-students', [AdminCourseAssignController::class, 'form'])->name('courses.assign');
        Route::post('courses/{course}/assign-students', [AdminCourseAssignController::class, 'store'])->name('courses.assign.store');
        Route::delete('courses/{course}/students/{user}', [AdminCourseAssignController::class, 'remove'])->name('courses.assign.remove');

        // Progress
        Route::get('courses/{course}/progress', [AdminCourseProgressController::class, 'show'])->name('courses.progress');
        Route::get('courses/{course}/students/{student}/progress', [AdminCourseProgressController::class, 'showStudent'])->name('courses.students.progress');
    });

/*
|--------------------------------------------------------------------------
| Instructor
|--------------------------------------------------------------------------
*/
Route::prefix('instructor')
    ->name('instructor.')
    ->middleware(['auth', 'role.active:instructor'])
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Instructor\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

        Route::resource('categories', InstructorCategoryController::class)->except(['show']);
        Route::resource('tags', InstructorTagController::class)->except(['show']);

        // Courses
        Route::get('courses', [InstructorCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/create', [InstructorCourseController::class, 'create'])->name('courses.create');
        Route::post('courses', [InstructorCourseController::class, 'store'])->name('courses.store');
        Route::get('courses/{course}/edit', [InstructorCourseController::class, 'edit'])->name('courses.edit');
        Route::patch('courses/{course}', [InstructorCourseController::class, 'update'])->name('courses.update');
        Route::patch('courses/{course}/status', [InstructorCourseController::class, 'updateStatus'])->name('courses.status.update');
        Route::post('courses/{course}/publish', [InstructorCourseController::class, 'publish'])->name('courses.publish');
        Route::delete('courses/{course}', [InstructorCourseController::class, 'destroy'])->name('courses.destroy');

        // Modules
        Route::post('courses/{course}/modules', [InstructorModuleController::class, 'store'])->name('modules.store');
        Route::patch('modules/{module}', [InstructorModuleController::class, 'update'])->name('modules.update');
        Route::delete('modules/{module}', [InstructorModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('courses/{course}/modules/reorder', [InstructorModuleController::class, 'reorder'])->name('modules.reorder');

        // Lessons
        Route::post('modules/{module}/lessons', [InstructorLessonController::class, 'store'])->name('lessons.store');
        Route::patch('lessons/{lesson}', [InstructorLessonController::class, 'update'])->name('lessons.update');
        Route::delete('lessons/{lesson}', [InstructorLessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('modules/{module}/lessons/reorder', [InstructorLessonController::class, 'reorder'])->name('lessons.reorder');

        // Quizzes
        Route::post('lessons/{lesson}/quiz', [InstructorQuizController::class, 'upsert'])->name('quizzes.upsert');
        Route::post('quizzes/{quiz}/questions', [InstructorQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
        Route::patch('questions/{question}', [InstructorQuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
        Route::delete('questions/{question}', [InstructorQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');

        // === Pre/Posttest Store ===
        Route::post('courses/{course}/pretest', [InstructorQuizController::class, 'storePretest'])->name('courses.pretest.store');
        Route::post('courses/{course}/posttest', [InstructorQuizController::class, 'storePosttest'])->name('courses.posttest.store');

        Route::post('courses/{course}/posttest/copy-from-pretest', [InstructorQuizController::class, 'syncFromPretest'])
            ->name('courses.posttest.copyFromPretest');

        Route::post('courses/{course}/quizzes/{kind}/import', [InstructorQuizController::class, 'importByKind'])
            ->name('courses.quizzes.import');

        // Assignments
        Route::get('courses/{course}/assign-students', [InstructorCourseAssignController::class, 'form'])->name('courses.assign');
        Route::post('courses/{course}/assign-students', [InstructorCourseAssignController::class, 'store'])->name('courses.assign.store');
        Route::delete('courses/{course}/students/{user}', [InstructorCourseAssignController::class, 'remove'])->name('courses.assign.remove');

        // Progress
        Route::get('courses/{course}/progress', [InstructorCourseProgressController::class, 'show'])->name('courses.progress');
        Route::get('courses/{course}/students/{student}/progress', [InstructorCourseProgressController::class, 'showStudent'])->name('courses.students.progress');
    });

/*
|--------------------------------------------------------------------------
| Util: cache clear (protected by key)
|--------------------------------------------------------------------------
*/
Route::get('/_util/cache', function () {
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

require __DIR__.'/auth.php';
