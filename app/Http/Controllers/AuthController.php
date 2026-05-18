<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showUsers()
    {
        return view('login', [
            'usersByLetter' => User::all()
                ->groupBy(function ($user) {
                    $first = strtoupper(substr($user->name, 0, 1));

                    return ctype_alpha($first) ? $first : '*';
                })
                ->sortKeys()
                ->pipe(function ($col) {
                    // Move '*' to the end if present
                    if ($col->has('*')) {
                        $star = $col->pull('*');
                        $col->put('*', $star);
                    }

                    return $col;
                }),
        ]);
    }

    public function loginAs(int $userId)
    {
        // https://laravel.com/docs/13.x/authentication#authenticate-a-user-by-id
        Auth::loginUsingId($userId);

        return redirect('/buy');
    }

    /**
     * https://laravel.com/docs/13.x/authentication#logging-out
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
