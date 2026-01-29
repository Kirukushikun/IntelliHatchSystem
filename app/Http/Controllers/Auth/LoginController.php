<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private function landingPathForAuthenticatedUser(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '/login';
        }

        return ((int) $user->user_type) === 0
            ? '/admin/form-collection'
            : '/user/form-collection';
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
            throw ValidationException::withMessages([
                'login' => ['The provided credentials do not match our records.'],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->landingPathForAuthenticatedUser());
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
