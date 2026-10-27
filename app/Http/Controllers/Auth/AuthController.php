<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle a login attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_email'    => ['required', 'email'],
            'user_password' => ['required', 'string'],
        ]);

        $user = User::where('user_email', $request->user_email)->first();

        if (!$user
            || !$user->is_active
            || !Hash::check($request->user_password, $user->user_password_hash)) {

            return back()
                ->withErrors(['login' => 'Invalid email or password.'])
                ->withInput($request->only('user_email'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}