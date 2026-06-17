<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barcode extends Model
{
    protected static function booted(): void
    {
        static::updated(function (Barcode $barcode) {
            if ($barcode->article_id === null) {
                $barcode->delete();
            }
        });
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
