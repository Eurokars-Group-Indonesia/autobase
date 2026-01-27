<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Additional rate limiting check (backup to route middleware)
        $key = 'login_attempts_' . $request->ip();
        if (Cache::has($key) && Cache::get($key) >= 5) {
            Log::warning('Rate limit exceeded for login', [
                'ip' => $request->ip(),
                'email' => $request->email
            ]);
            
            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again in 1 minute.',
            ])->onlyInput('email');
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Regenerate session to prevent session fixation
            $request->session()->regenerate();
            
            // Clear failed attempts
            Cache::forget($key);
            
            // Update last login
            Auth::user()->update([
                'last_login' => now(),
                'updated_by' => Auth::id()
            ]);
            
            // Log successful login
            Log::info('User logged in successfully', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->intended(route('dashboard'));
        }

        // Increment failed attempts
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinute());
        
        // Log failed attempt
        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'attempts' => $attempts,
            'user_agent' => $request->userAgent()
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        $userEmail = Auth::user()->email;
        
        Auth::logout();
        
        // Invalidate session
        $request->session()->invalidate();
        
        // Regenerate CSRF token
        $request->session()->regenerateToken();
        
        // Log logout
        Log::info('User logged out', [
            'user_id' => $userId,
            'email' => $userEmail,
            'ip' => $request->ip()
        ]);

        return redirect()->route('login');
    }
}
