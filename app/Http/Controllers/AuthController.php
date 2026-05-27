<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function listUsers()
    {
        return view('login', [
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
            return view('enter-pin', ['user' => $user]);
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
        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:5', 'unique:users,name'],
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
        return view('user-settings', ['user' => $user]);
    }

    public function updateSettings() {}
}
