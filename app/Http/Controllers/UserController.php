<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Rules\UserHasRole;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.users.index', [
            'users' => User::with('roles')->withTrashed()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'unique:users,name'],
            'password' => ['required', 'string'],
        ]);

        $user = new User;
        $user->name = $validated['username'];
        $user->password = $validated['password'];
        $user->save();

        return redirect()->route('users.edit', $user)->with('toast', [
            'type' => 'success',
            'message' => 'Nutzer wurde erstellt.',
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
    public function edit(User $user)
    {
        return view('pages.users.edit', [
            'user' => $user,
            'worlds' => User::role(UserRole::World)->get(),
            'vendors' => User::role(UserRole::Vendor)->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
        ]);

        try {
            $user->name = $validated['username'];
            $user->save();

            return redirect()->route('users.index')->with('toast', [
                'type' => 'success',
                'message' => 'Änderungen wurde gespeichert.',
            ]);

        } catch (UniqueConstraintViolationException) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Änderungen konnten nicht gespeichert werden.',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.edit', $user->id)->with('toast', [
            'type' => 'success',
            'message' => 'Nutzer wurde deaktiviert.',
        ]);
    }

    public function restore(User $user)
    {
        $user->restore();

        return redirect()->route('users.edit', $user->id)->with('toast', [
            'type' => 'success',
            'message' => 'Nutzer wurde reaktiviert.',
        ]);
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user->password = $validated['password'];
        $user->save();

        return redirect()
            ->route('users.edit', $user)
            ->with('toast', ['type' => 'success', 'message' => 'Passwort wurde gespeichert.']);
    }

    public function updateRoles(Request $request, User $user)
    {
        $roles = [];

        foreach (UserRole::cases() as $role) {
            if ($request->input($role->value)) {
                array_push($roles, $role->value);
            }
        }

        $user->syncRoles($roles);

        return redirect()
            ->route('users.edit', $user)
            ->with('toast', ['type' => 'success', 'message' => 'Rollen wurden aktualisiert.']);
    }

    public function updateAssignment(Request $request, User $user)
    {
        $validated = $request->validate([
            'assigned_world_id' => ['nullable', 'integer', new UserHasRole(UserRole::World->value)],
            'assigned_vendor_id' => ['nullable', 'integer', new UserHasRole(UserRole::Vendor->value)],
        ]);

        $user->assigned_world_id = $validated['assigned_world_id'] ?? null;
        $user->assigned_vendor_id = $validated['assigned_vendor_id'] ?? null;
        $user->save();

        return redirect()
            ->route('users.edit', $user)
            ->with('toast', ['type' => 'success', 'message' => 'Zuweisung wurde aktualisiert.']);
    }

    public function removePin(User $user)
    {
        $user->pin = null;
        $user->save();

        return redirect()
            ->route('users.edit', $user)
            ->with('toast', ['type' => 'success', 'message' => 'PIN entfernt.']);
    }
}
