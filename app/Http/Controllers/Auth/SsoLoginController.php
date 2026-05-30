<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SsoLoginController extends Controller
{
    /**
     * Handle SSO callback from DMLS.
     *
     * Flow:
     * 1. User clicks "LMS" on the DMLS sidebar.
     * 2. DMLS generates a signed JWT and redirects here with ?token=<JWT>.
     * 3. We decode the JWT, find the local user, and log them in.
     */
    public function __invoke(Request $request)
    {
        $token = $request->query('token') ?? $request->post('token');

        if (! $token) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'No SSO token provided.']);
        }

        // Read secret directly from env — no config caching issues
        $secretKey = env('DMLS_SSO_SECRET_KEY');

        if (! $secretKey) {
            Log::error('DMLS SSO: Secret key not configured (DMLS_SSO_SECRET_KEY).');
            return redirect()->route('login')
                ->withErrors(['sso' => 'SSO is not configured. Please contact the administrator.']);
        }

        try {
            // Allow 60 seconds of clock skew between DMLS and LMS servers
            JWT::$leeway = 60;
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        } catch (\Firebase\JWT\ExpiredException $e) {
            Log::warning('DMLS SSO: Token has expired.', ['error' => $e->getMessage()]);
            return redirect()->route('login')
                ->withErrors(['sso' => 'SSO token has expired. Please try again from DMLS.']);
        } catch (\Exception $e) {
            Log::warning('DMLS SSO: Token validation failed.', ['error' => $e->getMessage()]);
            return redirect()->route('login')
                ->withErrors(['sso' => 'Invalid SSO token. Please try again from DMLS.']);
        }

        // Extract user data from JWT payload
        $username   = $decoded->username ?? null;
        $email      = $decoded->email    ?? null;
        $name       = $decoded->name     ?? null;
        $externalId = $decoded->sub      ?? null;

        if (! $username && ! $email && ! $externalId) {
            Log::warning('DMLS SSO: JWT payload missing user identifiers.', [
                'payload' => (array) $decoded,
            ]);
            return redirect()->route('login')
                ->withErrors(['sso' => 'Insufficient user data in SSO token.']);
        }

        // Find user: try username → email → external_id (sub)
        $user = null;

        if ($username) {
            $user = User::where('username', $username)->first();
        }

        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        if (! $user && $externalId) {
            $user = User::where('external_id', $externalId)->first();
        }

        if (! $user) {
            Log::warning('DMLS SSO: No matching LMS account found.', [
                'username'    => $username,
                'email'       => $email,
                'external_id' => $externalId,
            ]);
            return redirect()->route('login')
                ->withErrors(['sso' => 'Your account has not been added to the LMS yet. Please contact the administrator.']);
        }

        // Check if user is active
        if (! $user->isActive()) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'Your account is inactive. Please contact the administrator.']);
        }

        // Log the user in
        if (Auth::check()) {
            Auth::logout();
        }

        Auth::login($user);
        $request->session()->regenerate();

        Log::info('DMLS SSO: User logged in.', [
            'user_id'  => $user->id,
            'username' => $user->username,
            'email'    => $user->email,
        ]);

        $activeRole = $user->active_role ?? $user->getRoleNames()->first();

        $redirect = match ($activeRole) {
            'admin'      => route('admin.dashboard'),
            'instructor' => route('instructor.dashboard'),
            'student'    => route('user.dashboard'),
            default      => route('user.dashboard'),
        };

        return redirect()->intended($redirect);
    }
}
