<?php

namespace App\Enums;

enum UserRole: string
{
    case TallyUser = 'tally_user';
    case Admin = 'admin';
    case Vendor = 'vendor';
    case World = 'world';

    public function displayName(): string
    {
        return match ($this) {
            UserRole::TallyUser => 'Tally User',
            UserRole::Admin => 'Administrator',
            UserRole::Vendor => 'Vendor',
            UserRole::World => 'World',
        };
    }

    public function icon(): ?string
    {
        return match ($this) {
            UserRole::TallyUser => null,
            UserRole::Admin => 'lucide-shield',
            UserRole::Vendor => 'lucide-store',
            UserRole::World => 'lucide-globe',
        };
    }
}
