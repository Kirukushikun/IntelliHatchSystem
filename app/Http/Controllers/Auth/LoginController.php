<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    private function landingPathForAuthenticatedUser(): string
    {
        $user = Auth::user();
        if (((int) $user->user_type) === 0) {
            return '/admin/dashboard';
        } else {
            return '/user/forms';
        }
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'user_type' => 'required|in:admin,user',
        ]);

        // Trim whitespace from username
        $credentials['username'] = trim($credentials['username']);

        // Check if user is locked out
        $lockoutKey = 'login_lockout_' . $credentials['username'];
        $attemptsKey = 'login_attempts_' . $credentials['username'];
        
        if ($request->session()->has($lockoutKey) && $request->session()->get($lockoutKey) > now()->timestamp) {
            $remainingTime = ceil(($request->session()->get($lockoutKey) - now()->timestamp) / 60);
            return back()->with('error', "Too many login attempts. Please try again in {$remainingTime} minutes.");
        }

        // Authenticate using username and password
        if (!Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            // Increment login attempts
            $attempts = $request->session()->get($attemptsKey, 0) + 1;
            $request->session()->put($attemptsKey, $attempts);
            
            // Lock out after 5 attempts for 10 minutes
            if ($attempts >= 5) {
                $lockoutUntil = now()->addMinutes(10)->timestamp;
                $request->session()->put($lockoutKey, $lockoutUntil);
                return back()->with('error', 'Too many failed login attempts. Account locked for 10 minutes.');
            }
            
            $remainingAttempts = 5 - $attempts;
            return back()->with('error', "The provided credentials do not match our records. {$remainingAttempts} attempts remaining.");
        }

        // Reset login attempts on successful authentication
        $request->session()->forget($attemptsKey);
        $request->session()->forget($lockoutKey);

        $user = Auth::user();
        
        // Verify user type matches selected login type
        $isAdmin = ((int) $user->user_type) === 0;
        if (($credentials['user_type'] === 'admin' && !$isAdmin) || ($credentials['user_type'] === 'user' && $isAdmin)) {
            Auth::logout();
            $loginType = $credentials['user_type'] === 'admin' ? 'admin' : 'user';
            return back()->with('error', "The account does not belong to an {$loginType}.");
        }

        $request->session()->regenerate();

        return redirect()->intended($this->landingPathForAuthenticatedUser())
            ->with('success', 'Welcome back!');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }
}