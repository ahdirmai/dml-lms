<?php

namespace App\Services\Integration;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InternalUserService
{
    protected string $baseUrl;

    protected ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.internal_users.base_url', ''), '/');
        $this->token = config('services.internal_users.token');
    }

    /**
     * Ambil data user dari DMLS API untuk keperluan preview integrasi.
     *
     * Endpoint: GET {baseUrl}/users
     * Docs ref: Consumer_API_Documentation.md § 6.1
     *
     * @return array daftar user hasil normalisasi
     */
    public function fetchUsersForPreview(array $filters = []): array
    {
        if (empty($this->baseUrl)) {
            Log::warning('InternalUserService: base_url belum dikonfigurasi.');

            return [];
        }

        // Query params sesuai API docs: department, status, limit
        $query = array_filter([
            'department' => $filters['department'] ?? null,
            'status' => $filters['status'] ?? null,
            'limit' => $filters['limit'] ?? 100,
        ], fn ($v) => ! is_null($v));

        try {
            $request = Http::acceptJson();

            if ($this->token) {
                // DMLS API menggunakan header X-API-KEY
                $request = $request->withHeaders([
                    'X-API-KEY' => $this->token,
                ]);
            }

            // DMLS endpoint: GET /api/v1/users
            $response = $request->get($this->baseUrl.'/users', $query);

            if (! $response->successful()) {
                Log::warning('InternalUserService: gagal fetch users dari DMLS API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            $json = $response->json();

            // Response structure: { data: [ { id_user, name_user, email_user, status_user, department, ... } ] }
            $rawUsers = $json['data'] ?? [];

            if (! is_array($rawUsers)) {
                return [];
            }

            // Normalisasi ke struktur standar yang dipakai seluruh aplikasi LMS
            return array_values(array_map(
                fn (array $record) => $this->mapExternalUserRecord($record),
                $rawUsers
            ));
        } catch (\Throwable $e) {
            Log::error('InternalUserService: exception saat fetch users dari DMLS API', [
                'error' => $e->getMessage(),
                'filters' => $filters,
            ]);

            return [];
        }
    }

    /**
     * Normalisasi satu record user dari DMLS API ke format standar LMS.
     *
     * DMLS fields → LMS fields:
     *   id_user        → external_id
     *   name_user      → full_name
     *   email_user     → email
     *   status_user    → status
     *   department     → department
     *   name_job_position → job_title  (jika tersedia)
     */
    protected function mapExternalUserRecord(array $record): array
    {
        return [
            'external_id' => Arr::get($record, 'id_user') ?? Arr::get($record, 'external_id'),
            'full_name' => Arr::get($record, 'name_user') ?? Arr::get($record, 'full_name') ?? Arr::get($record, 'name'),
            'email' => Arr::get($record, 'email_user') ?? Arr::get($record, 'email'),
            'username' => Arr::get($record, 'username'),
            'department' => Arr::get($record, 'department'),
            'job_title' => Arr::get($record, 'name_job_position') ?? Arr::get($record, 'job_title'),
            'manager_external_id' => Arr::get($record, 'manager_external_id'),
            'is_employee' => (bool) Arr::get($record, 'is_employee', true),
            'is_hr' => (bool) Arr::get($record, 'is_hr', false),
            'status' => Arr::get($record, 'status_user') ?? Arr::get($record, 'status'),
            'raw' => $record, // simpan versi mentah untuk debugging
        ];
    }
}
