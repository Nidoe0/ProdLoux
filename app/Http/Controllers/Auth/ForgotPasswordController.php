<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Notifications\AdminTemporaryPasswordNotification;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || $user->role !== 'admin') {
            return back()->withErrors(['email' => 'Cet email n’est pas associé à un compte administrateur.']);
        }

        // Générer un mot de passe temporaire, l'enregistrer et l'envoyer par email
        $temporary = Str::random(10);
        $user->password = Hash::make($temporary);
        $user->setRememberToken(Str::random(60));
        $user->save();

        // Notifier l'administrateur avec le mot de passe temporaire
        $user->notify(new AdminTemporaryPasswordNotification($temporary));

        return back()->with('status', 'Un mot de passe temporaire vient de vous être envoyé par e-mail. Utilisez-le pour vous connecter et changez-le ensuite.');
    }
}
