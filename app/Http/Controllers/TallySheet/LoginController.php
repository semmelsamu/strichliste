<?php

namespace App\Http\Controllers\TallySheet;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
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
}
