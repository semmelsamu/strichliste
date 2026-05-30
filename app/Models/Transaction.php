<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function buyArticleTransaction(): HasOne
    {
        return $this->hasOne(BuyArticleTransaction::class);
    }

    /**
     * If this transaciton is an UndoTransaction, returns it
     */
    public function undoTransaction(): HasOne
    {
        return $this->hasOne(UndoTransaction::class);
    }

    /**
     * If existing, returns the UndoTransaction that undoes this transaction
     */
    public function undone(): HasOne
    {
        return $this->hasOne(UndoTransaction::class, 'undone_transaction_id');
    }

    public static function normalize(Transaction $transaction): Transaction
    {
        if ($transaction->amount >= 0) {
            return $transaction;
        }

        $clone = clone $transaction;
        [$clone->fromUser, $clone->toUser] = [$clone->toUser, $clone->fromUser];
        $clone->amount = abs($clone->amount);

        return $clone;
    }
}
