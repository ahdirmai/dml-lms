<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk membuat user dengan role.
     */
    public function run(): void
    {
        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@dml-lms.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // ganti sesuai kebutuhan
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole(['admin']);

        // 2. Instructors
        for ($i = 1; $i <= 5; $i++) {
            $instructor = User::firstOrCreate(
                ['email' => "instructor{$i}@dml-lms.test"],
                [
                    'name' => "Instructor {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $instructor->assignRole(['instructor', 'student']);
        }

        // 3. Students
        for ($i = 1; $i <= 30; $i++) {
            $student = User::firstOrCreate(
                ['email' => "student{$i}@dml-lms.test"],
                [
                    'name' => "Student {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $student->assignRole('student');
        }

        $this->command->info('✅ User seeder berhasil dijalankan (1 admin, 5 instructor, 30 student).');
    }
}
