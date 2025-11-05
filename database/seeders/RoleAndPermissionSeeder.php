<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk role dan permission.
     */
    public function run(): void
    {
        // Daftar permission dasar (bisa disesuaikan sesuai modul LMS kamu)
        $permissions = [
            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Role & Permission Management
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            // Course Management
            'course.view',
            'course.create',
            'course.edit',
            'course.delete',

            // Tambahkan di daftar $permissions:
            'categories.manage',
            'tags.manage',


            // Lesson Management
            'lesson.view',
            'lesson.create',
            'lesson.edit',
            'lesson.delete',

            // Enrollment & Assessment
            'enrollment.view',
            'enrollment.approve',
            'quiz.manage',

            // System
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Definisikan role
        $roles = [

            'admin' => [
                'user.view',
                'user.create',
                'user.edit',
                'user.delete',
                'role.view',
                'role.create',
                'role.edit',
                'role.delete',
                'course.view',
                'course.create',
                'course.edit',
                'course.delete',
                'lesson.view',
                'lesson.create',
                'lesson.edit',
                'lesson.delete',
                'enrollment.view',
                'enrollment.approve',
                'quiz.manage',
                'settings.manage',
                'categories.manage',
                'tags.manage',
            ],
            'instructor' => [
                'course.view',
                'course.create',
                'course.edit',
                'lesson.view',
                'lesson.create',
                'lesson.edit',
                'quiz.manage',
            ],
            'student' => [
                'course.view',
                'lesson.view',
                'enrollment.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('✅ Role dan Permission berhasil dibuat!');
    }
}
