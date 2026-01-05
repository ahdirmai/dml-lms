<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserImportSession;
use App\Models\IntegrationLog;
use App\Services\Integration\InternalUserService;
use App\Services\Integration\UserSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserIntegrationController extends Controller
{
    /**
     * Tampilkan halaman utama Integrasi User.
     *
     * Route name: admin.integration.users.index
     * Method: GET
     */
    public function index()
    {
        // Kalau perlu kirim data awal (misalnya list department dari config),
        // bisa di-pass ke view di sini.
        return view('admin.pages.integrations.index');
    }

    /**
     * Ambil data user dari sistem internal untuk preview di UI.
     *
     * Route name: admin.integration.users.preview
     * Method: POST (AJAX)
     */
    public function preview(Request $request, InternalUserService $internalUserService)
    {
        $validated = $request->validate([
            'department' => ['nullable', 'string', 'max:255'],
            'status'     => ['nullable', 'string', 'max:50'], // mis: active/inactive
            'limit'      => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $filters = [
            'department' => $validated['department'] ?? null,
            'status'     => $validated['status'] ?? null,
            'limit'      => $validated['limit'] ?? 100,
        ];

        // 1. Panggil API internal untuk ambil user (array of associative array)
        $externalUsers = $internalUserService->fetchUsersForPreview($filters);

        // Dipastikan array
        $externalUsers = is_array($externalUsers) ? $externalUsers : [];

        // 2. Cek user yang sudah ada di LMS (by external_id)
        $externalIds = collect($externalUsers)
            ->pluck('external_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $existingUsers = User::whereIn('external_id', $externalIds)
            ->pluck('id', 'external_id'); // [external_id => id]

        // 3. Bentuk data untuk dikirim ke FE (jangan terlalu gemuk, hanya yang perlu)
        $previewData = collect($externalUsers)->map(function (array $u) use ($existingUsers) {
            $externalId = $u['external_id'] ?? null;

            return [
                'external_id'  => $externalId,
                'username'     => $u['username'] ?? null,
                'full_name'    => $u['full_name'] ?? null,
                'email'        => $u['email'] ?? null,
                'department'   => $u['department'] ?? null,
                'job_title'    => $u['job_title'] ?? null,
                'is_employee'  => (bool)($u['is_employee'] ?? true),
                'is_hr'        => (bool)($u['is_hr'] ?? false),
                'status'       => $u['status'] ?? null,
                'already_exists' => $externalId && $existingUsers->has($externalId),
            ];
        })->values()->all();

        // 4. Simpan session import di DB, supaya ketika import kita tidak percaya data dari FE
        $adminId = Auth::id();
        $sessionToken = Str::uuid()->toString();

        $session = UserImportSession::create([
            'admin_id'      => $adminId,
            'session_token' => $sessionToken,
            'filters'       => $filters,
            'payload'       => $externalUsers,   // simpan full payload dari internal
            'total_records' => count($externalUsers),
            'expires_at'    => Carbon::now()->addHours(1), // misal expired 1 jam
        ]);

        // 5. Optionally log aktivitas
        IntegrationLog::create([
            'admin_id'    => $adminId,
            'source'      => 'internal_system',
            'action'      => 'import_preview',
            'status'      => 'success',
            'message'     => 'Preview users: ' . $session->total_records,
        ]);

        return response()->json([
            'success'          => true,
            'import_session_id' => $sessionToken,
            'total'            => count($previewData),
            'data'             => $previewData,
        ]);
    }

    /**
     * Import user yang dipilih dari hasil preview ke database LMS.
     *
     * Route name: admin.integration.users.import
     * Method: POST
     */
    public function import(Request $request, UserSyncService $userSyncService)
    {
        $validated = $request->validate([
            'import_session_id'      => ['required', 'string'],
            'selected_external_ids'  => ['required', 'array', 'min:1'],
            'selected_external_ids.*' => ['required', 'string'],
        ]);

        $adminId   = Auth::id();
        $sessionId = $validated['import_session_id'];
        $selectedIds = array_unique($validated['selected_external_ids']);

        // 1. Ambil session import, pastikan milik admin yang sama dan belum expired
        /** @var UserImportSession|null $session */
        $session = UserImportSession::where('session_token', $sessionId)
            ->where('admin_id', $adminId)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })
            ->first();

        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Import session tidak ditemukan atau sudah expired.',
            ], 422);
        }

        $payload = collect($session->payload ?? [])
            ->keyBy('external_id'); // [external_id => userPayload]

        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed'  => 0,
            'errors'  => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($selectedIds as $externalId) {
                $userData = $payload->get($externalId);

                if (! $userData || empty($externalId)) {
                    $results['skipped']++;
                    $results['errors'][] = [
                        'external_id' => $externalId,
                        'reason'      => 'Data user tidak ditemukan di session payload.',
                    ];
                    continue;
                }

                try {
                    // Delegasikan ke service yang urus:
                    // - upsert ke users
                    // - upsert ke user_profiles
                    // - mapping Spatie roles (student/hr)
                    // Service ini sebaiknya return string 'created' / 'updated' / 'skipped'
                    $action = $userSyncService->syncUserFromExternalPayload($userData);

                    if ($action === 'created') {
                        $results['created']++;
                    } elseif ($action === 'updated') {
                        $results['updated']++;
                    } else {
                        $results['skipped']++;
                    }

                    IntegrationLog::create([
                        'admin_id'    => $adminId,
                        'source'      => 'internal_system',
                        'action'      => 'import_store',
                        'external_id' => $externalId,
                        'status'      => 'success',
                        'message'     => "User {$externalId} {$action}",
                    ]);
                } catch (\Throwable $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'external_id' => $externalId,
                        'reason'      => $e->getMessage(),
                    ];

                    IntegrationLog::create([
                        'admin_id'    => $adminId,
                        'source'      => 'internal_system',
                        'action'      => 'import_store',
                        'external_id' => $externalId,
                        'status'      => 'failed',
                        'message'     => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            IntegrationLog::create([
                'admin_id' => $adminId,
                'source'   => 'internal_system',
                'action'   => 'import_store',
                'status'   => 'failed',
                'message'  => 'Fatal error: ' . $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat proses import.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Proses import selesai.',
            'summary' => $results,
        ]);
    }
}
