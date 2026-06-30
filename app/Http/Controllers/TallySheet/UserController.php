<?php

namespace App\Http\Controllers\TallySheet;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Barcode;
use App\Models\User;
use App\Services\TallySheetSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private readonly TallySheetSessionService $tallySheetSessionService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.tally-sheet.users.register');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'unique:users,name'],
            'pin' => ['nullable', 'string'],
        ]);

        $username = $validated['username'];
        $pin = $validated['pin'] ?? null;

        $user = new User;
        $user->name = $username;
        $user->pin = $pin;
        $user->save();
        $user->assignRole(UserRole::Customer);

        $this->tallySheetSessionService->login($user);

        return redirect()
            ->route('tally-sheet.show-deposit')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Willkommen, '.$user->name.'!',
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return view('pages.tally-sheet.users.user-settings', [
            'user' => $this->tallySheetSessionService->get('user'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', Rule::unique('users', 'name')->ignore($user)],
        ]);

        $user->name = $validated['username'];
        $user->save();

        return redirect()
            ->route('tally-sheet.users.edit')
            ->with('toast', ['type' => 'success', 'message' => 'Nutzername geändert.']);
    }

    public function updatePin(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
            'pin' => ['required', 'string'],
        ]);

        $user->pin = $validated['pin'];
        $user->save();

        return redirect()
            ->route('tally-sheet.users.edit')
            ->with('toast', ['type' => 'success', 'message' => 'PIN geändert.']);
    }

    public function removePin(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        if ($user->pin && ! Hash::check((string) $request->input('pin'), $user->pin)) {
            return redirect()
                ->route('tally-sheet.users.edit')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Falsche PIN. Die PIN wurde nicht entfernt.',
                ]);
        }

        $user->pin = null;
        $user->save();

        return redirect()
            ->route('tally-sheet.users.edit')
            ->with('toast', ['type' => 'success', 'message' => 'PIN entfernt.']);
    }

    public function addBarcode(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
            'barcode' => ['required', 'string', 'unique:barcodes,barcode'],
        ]);

        $barcode = new Barcode;
        $barcode->barcode = $validated['barcode'];
        $barcode->user_id = $user->id;
        $barcode->save();

        return redirect()
            ->route('tally-sheet.users.edit')
            ->with('toast', ['type' => 'success', 'message' => 'Barcode wurde verknüpft.']);
    }

    public function removeBarcode(Request $request, Barcode $barcode): RedirectResponse
    {
        $barcode->user()->dissociate();
        $barcode->save();

        return redirect()
            ->route('tally-sheet.users.edit')
            ->with('toast', ['type' => 'success', 'message' => 'Barcode wurde entfernt.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        if ($user->pin && ! Hash::check((string) $request->input('pin'), $user->pin)) {
            return redirect()
                ->route('tally-sheet.users.edit')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Falsche PIN. Der Account wurde nicht deaktiviert.',
                ]);
        }

        $user->delete();
        $this->tallySheetSessionService->logout();

        return redirect()->route('tally-sheet.auth.list-users')->with('toast', [
            'type' => 'success',
            'message' => 'Account wurde erfolgreich deaktiviert.',
        ]);
    }
}
