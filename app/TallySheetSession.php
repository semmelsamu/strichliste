<?php

namespace App;

use App\Enums\UserType;
use App\Models\User;
use InvalidArgumentException;

class TallySheetSession
{
    public const UserSessionKey = 'tally_sheet.user_id';

    public function currentUser(): ?User
    {
        $userId = session(self::UserSessionKey);

        if (! $userId) {
            return null;
        }

        $user = User::query()
            ->whereKey($userId)
            ->where('type', UserType::NormalUser)
            ->first();

        if (! $user) {
            $this->forgetUser();
        }

        return $user;
    }

    public function selectUser(User $user): void
    {
        if ($user->type !== UserType::NormalUser || $user->trashed()) {
            throw new InvalidArgumentException('Only active normal users can be selected for the tally sheet session.');
        }

        session([self::UserSessionKey => $user->id]);
    }

    public function forgetUser(): void
    {
        session()->forget(self::UserSessionKey);
    }

    public function clear(): void
    {
        $this->forgetUser();
    }
}
