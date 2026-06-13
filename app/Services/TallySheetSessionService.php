<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use InvalidArgumentException;

class TallySheetSessionService
{
    public const WORLD_SESSION_KEY = 'tally_sheet.world_id';

    public const VENDOR_SESSION_KEY = 'tally_sheet.vendor_id';

    public const USER_SESSION_KEY = 'tally_sheet.user_id';

    public function initialize(User $worldUser, User $vendorUser)
    {
        $this->clear();

        if (! $worldUser->hasRole(UserRole::World) || $worldUser->trashed()) {
            throw new InvalidArgumentException('The given world user is invalid.');
        }
        if (! $vendorUser->hasRole(UserRole::Vendor) || $vendorUser->trashed()) {
            throw new InvalidArgumentException('The given vendor user is invalid.');
        }

        session([
            self::WORLD_SESSION_KEY => $worldUser->id,
            self::VENDOR_SESSION_KEY => $vendorUser->id,
        ]);
    }

    public function login(User $user): void
    {
        if (! $user->hasRole(UserRole::TallyUser) || $user->trashed()) {
            throw new InvalidArgumentException('Only active tally users can be selected for the tally sheet session.');
        }

        session([self::USER_SESSION_KEY => $user->id]);
    }

    public function logout(): void
    {
        session()->forget(self::USER_SESSION_KEY);
    }

    public function clear(): void
    {
        $this->logout();
        session()->forget(self::WORLD_SESSION_KEY);
        session()->forget(self::VENDOR_SESSION_KEY);
    }

    public function get(?string $key = null)
    {
        $worldId = session(self::WORLD_SESSION_KEY);
        $vendorId = session(self::VENDOR_SESSION_KEY);
        $userId = session(self::USER_SESSION_KEY);

        $session = [
            'world' => User::query()
                ->whereKey($worldId)
                ->role(UserRole::World)
                ->first(),
            'vendor' => User::query()
                ->whereKey($vendorId)
                ->role(UserRole::Vendor)
                ->first(),
            'user' => User::query()
                ->whereKey($userId)
                ->role(UserRole::TallyUser)
                ->first(),
        ];

        if ($key) {
            return $session[$key];
        } else {
            return $session;
        }
    }

    public function isRunning(): bool
    {
        return $this->get('world') && $this->get('vendor');
    }

    public function isLoggedIn(): bool
    {
        return (bool) $this->get('user');
    }
}
