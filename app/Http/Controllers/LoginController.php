<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login()
    {
        return view('pages.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'name' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $toast = [
                'type' => 'success',
                'message' => 'Willkommen, '.Auth::user()->name.'!',
            ];

            // Auto-redirect tally hosts to session screen
            if (Auth::user()->hasRole(UserRole::TallyHost)) {
                return redirect()->route('tally-sheet.start-session')->with('toast', $toast);
            }

            return redirect()->route('dashboard')->with('toast', $toast);
        }

        return back()->withErrors([
            'name' => 'Die angegebenen Zugangsdaten sind fehlerhaft.',
        ])->onlyInput('name');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login')->with('toast', [
            'type' => 'success',
            'message' => 'Erfolgreich abgemeldet.',
        ]);
    }
}
