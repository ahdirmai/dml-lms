<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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
        // 2. Instructors
        $instructorNames = [
            'Budi Santoso',
            'Siti Aminah',
            'Rina Wijaya',
            'Agus Pratama',
            'Dewi Lestari',
        ];

        foreach ($instructorNames as $index => $name) {
            $i = $index + 1;
            $emailName = strtolower(str_replace(' ', '.', $name));
            $instructor = User::firstOrCreate(
                ['email' => "{$emailName}@lms.test"],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $instructor->assignRole(['instructor', 'student']);

            // Create Profile
            \App\Models\UserProfile::firstOrCreate(
                ['user_id' => $instructor->id],
                [
                    'job_title' => 'Instructor',
                    'department' => 'Education',
                ]
            );
        }

        // 3. Students
        $studentNames = [
            'Andi Saputra', 'Bambang Pamungkas', 'Citra Kirana', 'Dedi Mulyadi', 'Eka Putri',
            'Fajar Nugraha', 'Gita Gutawa', 'Hendra Setiawan', 'Indah Permatasari', 'Joko Anwar',
            'Kartika Putri', 'Lukman Sardi', 'Maya Septha', 'Nina Zatulini', 'Oki Setiana',
            'Prilly Latuconsina', 'Qory Sandioriva', 'Raffi Ahmad', 'Sandra Dewi', 'Titi Kamal',
            'Uya Kuya', 'Vino G Bastian', 'Wulan Guritno', 'Xavier Hernandez', 'Yuni Shara',
            'Zaskia Adya Mecca', 'Arif Muhammad', 'Bayu Skak', 'Chandra Liow', 'Deddy Corbuzier',
        ];

        foreach ($studentNames as $index => $name) {
            $i = $index + 1;
            $student = User::firstOrCreate(
                ['email' => "student{$i}@lms.test"],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $student->assignRole('student');

            // Create Profile
            \App\Models\UserProfile::firstOrCreate(
                ['user_id' => $student->id],
                [
                    'job_title' => collect(['Staff', 'Officer', 'Analyst', 'Intern'])->random(),
                    'department' => collect(['Operations', 'IT', 'HR', 'Finance', 'Marketing'])->random(),
                ]
            );
        }

        $this->command->info('✅ User seeder berhasil dijalankan (1 admin, 5 instructor, 30 student).');
    }
}
