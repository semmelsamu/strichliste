<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function depositMoney(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => ['required', 'decimal:0,2'],
                'world' => ['required', Rule::exists('users', 'id')->where('type', UserType::World->value)],
                'user' => ['required', 'exists:users,id'],
            ]);

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Action completed successfully!',
            ]);

        } catch (\Exception $e) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Something went wrong: '.$e->getMessage(),
            ]);
        }
    }
}
