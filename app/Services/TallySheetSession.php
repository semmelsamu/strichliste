<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\User;
use InvalidArgumentException;

class TallySheetSession
{
    public const WORLD_SESSION_KEY = 'tally_sheet.world_id';

    public const VENDOR_SESSION_KEY = 'tally_sheet.vendor_id';

    public const USER_SESSION_KEY = 'tally_sheet.user_id';

    public function initialize(User $world, User $vendor)
    {
        if ($world->type !== UserType::World || $world->trashed()) {
            throw new InvalidArgumentException('The given world user is invalid.');
        }
        if ($vendor->type !== UserType::Vendor || $vendor->trashed()) {
            throw new InvalidArgumentException('The given vendor user is invalid.');
        }

        session([
            self::WORLD_SESSION_KEY => $world->id,
            self::VENDOR_SESSION_KEY => $vendor->id,
        ]);
    }

    public function login(User $user): void
    {
        if ($user->type !== UserType::NormalUser || $user->trashed()) {
            throw new InvalidArgumentException('Only active normal users can be selected for the tally sheet session.');
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

    public function get(?string $key)
    {
        $worldId = session(self::WORLD_SESSION_KEY);
        $vendorId = session(self::VENDOR_SESSION_KEY);
        $userId = session(self::USER_SESSION_KEY);

        $session = [
            'world' => User::query()
                ->whereKey($worldId)
                ->where('type', UserType::World)
                ->first(),
            'vendor' => User::query()
                ->whereKey($vendorId)
                ->where('type', UserType::Vendor)
                ->first(),
            'user' => User::query()
                ->whereKey($userId)
                ->where('type', UserType::NormalUser)
                ->first(),
        ];

        if ($key) {
            return $session[$key];
        } else {
            return $session;
        }
    }
}
