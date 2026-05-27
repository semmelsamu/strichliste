<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
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

    public function login(Request $request)
    {
        $validated = $request->validate([
            'user' => ['required', 'exists:users,id'],
            'pin' => [
                'required',
                'string',
                Rule::exists('users', 'pin')->where('id', $request->user),
            ],
        ]);

        return redirect()
            ->route('tally-sheet.deposit', [
                'user' => $validated['user'],
            ]);
    }
}
