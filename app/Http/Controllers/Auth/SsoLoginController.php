<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UsedSsoToken;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SsoLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $jwt = $request->get('token') ?? $request->post('token');

        if (! $jwt) {
            abort(401, 'Missing SSO token');
        }

        try {
            $config = config('sso');
            $algo = $config['algo'];
            $iss = $config['iss'];
            $aud = $config['aud'];
            $leeway = $config['leeway'] ?? 300;

            JWT::$leeway = $leeway;

            //
            // return $algo;
            if ($algo === 'RS256') {
                $keyMaterial = $config['public_key'];
                if (! $keyMaterial) {
                    throw new \RuntimeException('SSO public key not configured');
                }
                $key = new Key($keyMaterial, 'RS256');
            } else {
                // return 'x';
                $secret = $config['secret'];
                if (! $secret) {
                    throw new \RuntimeException('SSO secret not configured');
                }
                $key = new Key($secret, 'HS256');
            }

            // Decode & verify signature + exp
            //             dd([
            //     'config_secret' => $secret,
            //     'key_object' => $key
            // ]);
            $decoded = JWT::decode($jwt, $key);

            $claims = (array) $decoded;
            // Validasi iss & aud
            if (($claims['iss'] ?? null) !== $iss) {
                abort(401, 'Invalid issuer');
            }

            if (($claims['aud'] ?? null) !== $aud) {
                abort(401, 'Invalid audience');
            }

            // Validasi jti untuk anti-replay
            $jti = $claims['jti'] ?? null;
            if (! $jti) {
                abort(401, 'Missing jti');
            }

            $alreadyUsed = UsedSsoToken::where('jti', $jti)->exists();
            if ($alreadyUsed) {
                abort(401, 'SSO token already used');
            }

            // Optional: cek max_age manual (selain exp)
            $maxAge = $config['max_age'] ?? 300;
            $iat = $claims['iat'] ?? null;
            if (! $iat || (time() - $iat) > $maxAge) {
                abort(401, 'SSO token too old');
            }

            // Simpan sebagai sudah digunakan
            UsedSsoToken::create([
                'jti' => $jti,
                'used_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);

            // Ambil user berdasarkan external_id = sub
            $externalId = $claims['sub'] ?? null;
            if (! $externalId) {
                abort(401, 'Missing subject');
            }

            /** @var User|null $user */
            $user = User::where('external_id', $externalId)->first();

            if (! $user) {
                // Bisa pilih: auto-provision atau tolak.
                // Untuk aman, tolak dulu.
                abort(403, 'User not provisioned in LMS');
            }

            // Login user ke LMS
            // check auth, jika ada yang sedang login, logout dulu
            if (Auth::check()) {
                Auth::logout();
            }
            Auth::login($user, false);

            $activeRole = $user->active_role ?? $user->getRoleNames()->first();
            // Redirect ke dashboard atau route yang kamu mau
            $redirect = match ($activeRole) {
                'admin' => route('admin.dashboard'),
                'instructor' => route('instructor.dashboard'),
                'student' => route('user.dashboard'),
                default => route('user.dashboard'),
            };

            return redirect()->intended($redirect);
        } catch (\Throwable $e) {
            Log::warning('SSO JWT failed', [
                'error' => $e->getMessage(),
            ]);

            // return $e->getMessage();
            abort(401, 'Invalid SSO token');
        }
    }
}
