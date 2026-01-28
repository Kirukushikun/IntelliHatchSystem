<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
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
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'password' => 'required|string',
        ]);

        // For simple authentication, we'll check by first_name + last_name + password
        // In a real app, you might want to add a unique identifier like email or username
        
        $user = \App\Models\User::where('first_name', $credentials['first_name'])
                                ->where('last_name', $credentials['last_name'])
                                ->first();

        if (!$user || !Auth::attempt(['id' => $user->id, 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials do not match our records.'],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/incubator-routine');
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
