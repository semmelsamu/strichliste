<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function listUsers()
    {
        return view('pages.tally-sheet.login', [
            'usersByLetter' => User::groupByFirstLetter(
                User::where('type', UserType::NormalUser)->get()
            ),
        ]);
    }

    private function userStartPage($user)
    {
        if ($user->balance <= 0) {
            return redirect()
                ->route('tally-sheet.deposit', [
                    'user' => $user,
                ]);
        } else {
            return redirect()
                ->route('tally-sheet.buy-overview', [
                    'user' => $user,
                ]);
        }
    }

    public function login(Request $request, User $user)
    {
        if ($user->pin) {
            return view('pages.tally-sheet.enter-pin', ['user' => $user]);
        } else {
            return $this->userStartPage($user);
        }
    }

    public function validatePin(Request $request)
    {
        $validated = $request->validate([
            'user' => ['required', 'exists:users,id'],
            'pin' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $user = User::find($request->user);
                    if (! $user || ! Hash::check($value, $user->pin)) {
                        $fail('The provided PIN is incorrect.');
                    }
                },
            ],
        ]);

        return $this->userStartPage(User::find($validated['user']));
    }

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
