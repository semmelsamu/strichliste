<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function registerForm()
    {
        return view('pages.tally-sheet.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'unique:users,name'],
            'pin' => ['nullable', 'string'],
        ]);

        $username = $validated['username'];
        $pin = $validated['pin'];

        $user = new User;
        $user->name = $username;
        $user->pin = $pin;
        $user->save();

        return redirect()
            ->route('tally-sheet.deposit', [
                'user' => $user->id,
            ])
            ->with('toast', [
                'type' => 'success',
                'message' => 'Willkommen, '.$user->name.'!',
            ]);
    }

    public function settings(User $user)
    {
        return view('pages.tally-sheet.user-settings', ['user' => $user]);
    }

    public function updateUsername(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'unique:users,name'],
        ]);

        $user->name = $validated['username'];
        $user->save();

        return redirect()
            ->route('tally-sheet.auth.show-settings', $user)
            ->with('toast', ['type' => 'success', 'message' => 'Nutzername geändert.']);
    }

    public function updatePin(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string'],
        ]);

        $user->pin = $validated['pin'];
        $user->save();

        return redirect()
            ->route('tally-sheet.auth.show-settings', $user)
            ->with('toast', ['type' => 'success', 'message' => 'PIN geändert.']);
    }

    public function removePin(User $user): RedirectResponse
    {
        $user->pin = null;
        $user->save();

        return redirect()
            ->route('tally-sheet.auth.show-settings', $user)
            ->with('toast', ['type' => 'success', 'message' => 'PIN entfernt.']);
    }

    public function deactivate(User $user)
    {
        $user->delete();

        return redirect()->route('tally-sheet.auth.list-users')->with('toast', [
            'type' => 'success',
            'message' => 'Account wurde erfolgreich deaktiviert.',
        ]);
    }
}
