<?php

namespace App\Http\Controllers\TallySheet;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\UserHasRole;
use App\Services\TallySheetSessionService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(
        private readonly TallySheetSessionService $tallySheetSession,
    ) {}

    public function startSession(Request $request)
    {
        $user = $request->user();
        $world = $user->assignedWorld;
        $vendor = $user->assignedVendor;

        // Auto-start session if user has assigned world and vendor
        if ($world?->hasRole(UserRole::World) && $vendor?->hasRole(UserRole::Vendor)) {
            $this->tallySheetSession->initialize($world, $vendor);

            return redirect()->route('tally-sheet.auth.list-users');
        }

        if (! $request->query('world') || ! $request->query('vendor')) {
            return view('pages.tally-sheet.start-session', [
                'vendors' => User::role(UserRole::Vendor)->get(),
                'worlds' => User::role(UserRole::World)->get(),
            ]);
        }

        $validated = $request->validate([
            'world' => [
                'required',
                'integer',
                new UserHasRole(UserRole::World->value),
            ],
            'vendor' => [
                'required',
                'integer',
                new UserHasRole(UserRole::Vendor->value),
            ],
        ]);

        $this->tallySheetSession->initialize(
            User::find($validated['world']),
            User::find($validated['vendor']),
        );

        return redirect()->route('tally-sheet.auth.list-users');
    }

    public function stopSession()
    {
        $this->tallySheetSession->clear();

        return redirect()->route('dashboard')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Strichlisten-Session beendet.',
            ]);
    }
}
