<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barcode extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Barcode $barcode) {
            if ($barcode->article_id !== null && $barcode->user_id !== null) {
                throw new \LogicException('A barcode cannot be linked to both an article and a user.');
            }
        });

        static::updated(function (Barcode $barcode) {
            if ($barcode->article_id === null && $barcode->user_id === null) {
                $barcode->delete();
            }
        });
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
