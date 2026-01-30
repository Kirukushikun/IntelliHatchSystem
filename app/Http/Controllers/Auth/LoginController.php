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

        if (!$user) {
            return '/login';
        }

        return ((int) $user->user_type) === 0
            ? '/admin/dashboard'
            : '/user/forms';
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
        ]);

        // Authenticate using username and password
        if (!Auth::attempt($credentials)) {
            return back()->with('error', 'The provided credentials do not match our records.');
        }

        $user = Auth::user();
        $isAdmin = ((int) $user->user_type) === 0;
        
        // Check if the login route matches the user type
        $isAdminRoute = $request->path() === 'admin/login' || $request->get('admin') === 'true';
        
        if ($isAdminRoute && !$isAdmin) {
            // User trying to login through admin route
            Auth::logout();
            return back()->with('error', 'Access denied. This is the admin login portal. Please use the user login.');
        }
        
        if (!$isAdminRoute && $isAdmin) {
            // Admin trying to login through user route
            Auth::logout();
            return back()->with('error', 'Access denied. This is the user login portal. Please use the admin login.');
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