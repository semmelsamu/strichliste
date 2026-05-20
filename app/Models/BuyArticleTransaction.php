<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(key: 'transaction_id', incrementing: false, timestamps: false)]
class BuyArticleTransaction extends Model
{
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
