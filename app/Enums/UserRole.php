<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case TallyHost = 'tally_host';
    case Vendor = 'vendor';
    case World = 'world';

    public function displayName(): string
    {
        return match ($this) {
            UserRole::Customer => 'Kunde',
            UserRole::Admin => 'Administrator',
            UserRole::TallyHost => 'Strichlisten-Host',
            UserRole::Vendor => 'Verkäufer',
            UserRole::World => 'Außenwelt',
        };
    }

    public function icon(): ?string
    {
        return match ($this) {
            UserRole::Customer => null,
            UserRole::Admin => 'lucide-shield',
            UserRole::TallyHost => 'lucide-tally-5',
            UserRole::Vendor => 'lucide-store',
            UserRole::World => 'lucide-globe',
        };
    }

    public function description(): string
    {
        return match ($this) {
            UserRole::Customer => 'Nutzer mit dieser Rolle können sich in der Strichliste anmelden, Geld aufladen und Artikel kaufen.',
            UserRole::Admin => 'Administratoren haben alle Rechte zum Verwalten der Strichliste.',
            UserRole::Vendor => 'Ein Kauf eines Artikels wird mit einer Transaktion von einem Nutzerkonto auf ein Verkäuferkonto erfasst.',
            UserRole::World => 'Wird Geld auf ein Konto eingezahlt, wird eine Transaktion der Außenwelt zum Nutzerkonto erstellt.',
            UserRole::TallyHost => 'Strichlisten-Hosts haben die Berechtigung, eine Strichlisten-Session zu starten.',
        };
    }
}
