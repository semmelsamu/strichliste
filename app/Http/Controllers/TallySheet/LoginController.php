<?php

namespace App\Http\Controllers\TallySheet;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TallySheetSessionService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct(private readonly TallySheetSessionService $tallySheetSessionService) {}

    public function listUsers()
    {
        return view('pages.tally-sheet.auth.login', [
            'usersByLetter' => User::groupByFirstLetter(
                User::where('type', UserType::NormalUser)->get()
            ),
        ]);
    }

    private function userStartPage(User $user): RedirectResponse
    {
        $this->tallySheetSessionService->login($user);

        if ($user->balance <= 0) {
            return redirect()->route('tally-sheet.show-deposit');
        }

        return redirect()->route('tally-sheet.buy-overview');
    }

    public function login(Request $request, User $user)
    {
        if ($user->type !== UserType::NormalUser) {
            return redirect()->route('tally-sheet.auth.list-users');
        }

        if ($user->pin) {
            return view('pages.tally-sheet.auth.enter-pin', ['user' => $user]);
        }

        return $this->userStartPage($user);
    }

    public function validatePin(Request $request, User $user): RedirectResponse
    {
        if ($user->type !== UserType::NormalUser) {
            return redirect()->route('tally-sheet.auth.list-users');
        }

        $request->validate([
            'pin' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    if (! Hash::check($value, $user->pin)) {
                        $fail('The provided PIN is incorrect.');
                    }
                },
            ],
        ]);

        return $this->userStartPage($user);
    }

    public function logout(): RedirectResponse
    {
        $this->tallySheetSessionService->logout();

        return redirect()->route('tally-sheet.auth.list-users');
    }
}
