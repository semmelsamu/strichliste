<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function depositMoney(Request $request)
    {
        try {
            $validated = $request->validate([
                'action' => ['required', Rule::in(['deposit', 'withdraw'])],
                'world' => [
                    'required',
                    Rule::exists('users', 'id')->where('type', UserType::World->value),
                ],
                'user' => [
                    'required',
                    'exists:users,id',
                    'bail',
                ],
                'amount' => [
                    'required',
                    'decimal:0,2',
                    function (string $attribute, mixed $value, Closure $fail) {
                        $user = User::find(request()->user);
                        if (request()->action == 'withdraw') {
                            $value *= -1;
                        }
                        if ($user->balance + $value < 0) {
                            $fail('Du kannst nicht mehr abbuchen als du auf dem Konto hast.');
                        }
                    },
                ],
            ]);

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Action completed successfully!',
            ]);

        } catch (Exception $e) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Something went wrong: '.$e->getMessage(),
            ]);
        }
    }
}
