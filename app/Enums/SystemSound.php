<?php

namespace App\Enums;

enum SystemSound: string
{
    case Deposit = 'deposit';
    case Withdraw = 'withdraw';
    case BuyFallback = 'buy_fallback';
    case UndoTransaction = 'undo_transaction';
    case Error = 'error';

    public function displayName(): string
    {
        return match ($this) {
            SystemSound::Deposit => 'Geld einzahlen',
            SystemSound::Withdraw => 'Geld auszahlen',
            SystemSound::BuyFallback => 'Fallbacksound beim Kauf',
            SystemSound::UndoTransaction => 'Transaktion rückgängig gemacht',
            SystemSound::Error => 'Fehler',
        };
    }
}
