<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(key: 'transaction_id', incrementing: false, timestamps: false)]
class UndoTransaction extends Model
{
    use HasFactory;

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function undoneTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'undone_transaction_id');
    }
}
