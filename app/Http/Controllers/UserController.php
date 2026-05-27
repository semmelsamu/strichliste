<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
}
