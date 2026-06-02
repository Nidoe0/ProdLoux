<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Email ou mot de passe incorrect.'])
                ->withInput($request->only('email'));
        }

        $user = Auth::user();

        // Only seller and admin can access the Blade dashboard
        if (! in_array($user->role, ['seller', 'admin'])) {
            Auth::logout();
            return back()->withErrors(['email' => 'Accès réservé aux vendeurs et administrateurs.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('vendor.dashboard'));
    }
}
