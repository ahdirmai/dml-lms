<?php

namespace App\Services\Integration;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class UserSyncService
{
    /**
     * Sinkronisasi satu user dari payload eksternal.
     *
     * @param  array  $payload  hasil normalisasi dari InternalUserService::mapExternalUserRecord
     * @return string 'created' | 'updated' | 'skipped'
     */
    public function syncUserFromExternalPayload(array $payload): string
    {
        $externalId = $payload['external_id'] ?? null;

        if (! $externalId) {
            throw new InvalidArgumentException('external_id wajib ada pada payload.');
        }

        // Email boleh kamu buat wajib, atau kalau kosong kamu putuskan skip.
        $email = $payload['email'] ?? null;

        if (! $email) {
            // Kalau mau kamu bisa lempar exception, saya buat skip saja contoh ini.
            return 'skipped';
        }

        return DB::transaction(function () use ($payload, $externalId, $email) {
            // 1. Upsert user (tabel users)
            /** @var User|null $user */
            $user = User::where('external_id', $externalId)->first();

            $userData = [
                'external_id' => $externalId,
                'name' => $payload['full_name'] ?? $payload['name'] ?? $email,
                'email' => $email,
                'lms_status' => $this->mapStatus($payload['status'] ?? null),
                'password' => Hash::make('password'),
            ];

            $action = 'created';

            if ($user) {
                $user->fill($userData);
                $user->save();
                $action = 'updated';
            } else {
                $user = User::create($userData);
            }

            // 2. Upsert profile (tabel user_profiles)
            $profileData = [
                'department' => $payload['department'] ?? null,
                'job_title' => $payload['job_title'] ?? null,
                'manager_external_id' => $payload['manager_external_id'] ?? null,
                'is_employee' => (bool) ($payload['is_employee'] ?? true),
                'is_hr' => (bool) ($payload['is_hr'] ?? false),
                'raw_payload' => $payload['raw'] ?? $payload,
            ];

            /** @var UserProfile $profile */
            $profile = $user->profile()
                ->updateOrCreate([], $profileData);

            // 3. Mapping role Spatie (student/hr) berdasarkan profile
            $this->syncRolesFromProfile($user, $profile);

            return $action;
        });
    }

    /**
     * Mapping status eksternal ke status LMS internal.
     *
     * Di sini kamu definisikan sekali saja: status dari HR/internal → lms_status.
     */
    protected function mapStatus(?string $externalStatus): string
    {
        if (! $externalStatus) {
            return User::STATUS_ACTIVE;
        }

        $normalized = strtolower(trim($externalStatus));

        return match ($normalized) {
            'active', 'aktif' => User::STATUS_ACTIVE,
            'inactive',
            'non_active',
            'nonaktif',
            'resigned',
            'terminated',
            'terminated_employee',
            'suspended' => User::STATUS_INACTIVE,

            default => User::STATUS_ACTIVE,
        };
    }

    /**
     * Sinkronisasi role Spatie berdasarkan profile.
     *
     * Catatan:
     * - Admin TIDAK disentuh oleh integrasi. Role 'admin' tetap aman.
     * - assignRole/removeRole aman dipanggil berulang (idempotent).
     */
    protected function syncRolesFromProfile(User $user, UserProfile $profile): void
    {
        // Student (employee)
        if ($profile->is_employee) {
            $user->assignRole('student');
        } else {
            $user->removeRole('student');
        }

        // HR / Instructor
        if ($profile->is_hr) {
            $user->assignRole('instructor');
        } else {
            $user->removeRole('instructor');
        }

        // Jangan pernah removeRole('admin') di sini.
    }
}
