<?php

namespace App\Http\Controllers;

use App\Models\User;

class TransactionController extends Controller
{
    public function depositMoney(User $world, User $user, double $amount)
    {
        try {
            // Your logic here
            throw new \Exception('Something went wrong'); // simulate failure

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
