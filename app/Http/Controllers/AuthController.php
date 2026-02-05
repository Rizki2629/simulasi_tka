<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $identifier = $validated['email'];
        $password = $validated['password'];

        $remember = $request->filled('remember');

        // Admin/Guru login should use email. Student login is handled via /simulasi/login.
        $ok = Auth::attempt(['email' => $identifier, 'password' => $password], $remember);

        if ($ok) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Username atau password salah.',
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
