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

    public function description(): string
    {
        return match ($this) {
            UserRole::TallyUser => 'Nutzer mit dieser Rolle können sich in der Strichliste anmelden, Geld aufladen und Artikel kaufen.',
            UserRole::Admin => 'Administratoren haben alle Rechte zum Verwalten der Strichliste.',
            UserRole::Vendor => 'Ein Kauf eines Artikels wird mit einer Transaktion von einem Nutzerkonto auf ein Vendor-Konto erfasst.',
            UserRole::World => 'Wird Geld auf ein Konto eingezahlt, wird eine Transaktion von einem World-Nutzer zum Nutzerkonto erstellt.',
        };
    }
}
