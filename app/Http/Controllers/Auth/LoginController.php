<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = 'login-' . Str::transliterate(Str::lower($request->email)) . '|' . $request->ip();

        // Check rate limit: 5 attempts per 60 seconds
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Clear rate limiter on success
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is deactivated.']);
            }

            if ($user->role === UserRole::SUPER_ADMIN) {
                return redirect()->intended('/admin');
            } elseif ($user->role === UserRole::FACULTY) {
                return redirect()->intended('/faculty');
            }

            return redirect()->intended('/dashboard');
        }

        // Increment rate limiter on failed attempt
        RateLimiter::hit($throttleKey, 60);

        // Log failed login attempt to audit_logs
        AuditLog::create([
            'user_id' => null,
            'action' => 'failed_login',
            'auditable_type' => 'App\\Models\\User',
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => ['email' => $request->email],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
