<?php

namespace App\Http\Controllers\TallySheet;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TallySheetSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SessionController extends Controller
{
    public function __construct(
        private readonly TallySheetSession $tallySheetSession,
    ) {}

    public function startSession(Request $request)
    {
        if (! $request->query('world') || ! $request->query('vendor')) {
            return view('pages.tally-sheet.start-session', [
                'vendors' => User::where('type', UserType::Vendor)->get(),
                'worlds' => User::where('type', UserType::World)->get(),
            ]);
        }

        $validated = $request->validate([
            'world' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('type', UserType::World->value),
            ],
            'vendor' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('type', UserType::Vendor->value),
            ],
        ]);

        $this->tallySheetSession->initialize(
            User::find($validated['world']),
            User::find($validated['vendor']),
        );

        return redirect()->route('tally-sheet.auth.list-users');
    }
}
