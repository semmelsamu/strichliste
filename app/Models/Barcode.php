<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barcode extends Model
{
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
