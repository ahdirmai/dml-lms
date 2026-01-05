<?php

namespace App\Services\Integration;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InternalUserService
{
    protected string $baseUrl;

    protected ?string $token;

    protected bool $mock;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.internal_users.base_url', ''), '/');
        $this->token = config('services.internal_users.token');
        $this->mock = (bool) config('services.internal_users.mock', false);
    }

    /**
     * Ambil data user dari sistem internal untuk keperluan preview integrasi.
     *
     * @return array daftar user hasil normalisasi
     */
    public function fetchUsersForPreview(array $filters = []): array
    {
        // Sementara pakai dummy kalau mock diaktifkan
        if ($this->mock || empty($this->baseUrl)) {
            return $this->generateDummyUsers($filters);
        }

        $query = array_filter([
            'department' => $filters['department'] ?? null,
            'status' => $filters['status'] ?? null,
            'limit' => $filters['limit'] ?? 100,
        ], fn ($v) => ! is_null($v));

        try {
            $request = Http::acceptJson();

            if ($this->token) {
                // Sesuai request: LMS wajib mengirimkan X-API-KEY
                $request = $request->withHeader('X-API-KEY', $this->token);
            }

            $response = $request->get($this->baseUrl.'/lms/users', $query);

            if (! $response->successful()) {
                Log::warning('InternalUserService: gagal fetch users', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            $json = $response->json();

            // Asumsi struktur: { data: [ ... ], meta: ... }
            $rawUsers = $json['data'] ?? $json ?? [];

            if (! is_array($rawUsers)) {
                return [];
            }

            // Normalisasi ke struktur standar yang dipakai seluruh aplikasi
            return array_values(array_map(
                fn (array $record) => $this->mapExternalUserRecord($record),
                $rawUsers
            ));
        } catch (\Throwable $e) {
            Log::error('InternalUserService: exception saat fetch users', [
                'error' => $e->getMessage(),
                'filters' => $filters,
            ]);

            return [];
        }
    }

    /**
     * Generator dummy users untuk dev/testing.
     */
    protected function generateDummyUsers(array $filters = []): array
    {
        $departments = ['Sales', 'HR', 'IT', 'Finance', 'Operations'];
        $statuses = ['active', 'inactive', 'resigned', 'terminated'];

        $limit = (int) ($filters['limit'] ?? 20);
        $limit = max(1, min($limit, 200));
        $filterDept = $filters['department'] ?? null;
        $filterStat = $filters['status'] ?? null;

        $users = [];

        for ($i = 1; $i <= $limit; $i++) {
            $dept = $filterDept ?: $departments[($i - 1) % count($departments)];
            $status = $filterStat ?: $statuses[($i - 1) % count($statuses)];

            $isEmployee = $status === 'terminated'
                ? false
                : true;

            $isHr = $dept === 'HR';

            $record = [
                'external_id' => sprintf('EMP%04d', $i),
                'full_name' => "Dummy User {$i}",
                'email' => "dummy{$i}@example.com",
                'department' => $dept,
                'job_title' => $isHr ? 'HR Specialist' : 'Staff',
                'manager_external_id' => $i <= 3 ? null : sprintf('EMP%04d', max(1, $i % 3)), // manager 1-3
                'is_employee' => $isEmployee,
                'is_hr' => $isHr,
                'status' => $status,
            ];

            $users[] = $this->mapExternalUserRecord($record);
        }

        return $users;
    }

    /**
     * Normalisasi satu record user dari sistem internal ke format standar kita.
     */
    protected function mapExternalUserRecord(array $record): array
    {
        // Sesuaikan key sesuai API internal mereka
        return [
            'external_id' => Arr::get($record, 'external_id') ?? Arr::get($record, 'employee_id'),
            'full_name' => Arr::get($record, 'full_name') ?? Arr::get($record, 'name'),
            'email' => Arr::get($record, 'email'),
            'username' => Arr::get($record, 'username'),
            'department' => Arr::get($record, 'department'),
            'job_title' => Arr::get($record, 'job_title'),
            'manager_external_id' => Arr::get($record, 'manager_external_id'),
            'is_employee' => (bool) Arr::get($record, 'is_employee', true),
            'is_hr' => (bool) Arr::get($record, 'is_hr', false),
            'status' => Arr::get($record, 'status'),
            'raw' => $record, // kalau mau simpan versi mentah
        ];
    }
}
