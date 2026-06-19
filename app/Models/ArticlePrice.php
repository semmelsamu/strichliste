<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(timestamps: false)]
class ArticlePrice extends Model
{
    protected static function booted(): void
    {
        $forgetCurrentPrice = function (ArticlePrice $price): void {
            Article::forgetCachedCurrentPrice($price->article_id);
        };

        static::saved($forgetCurrentPrice);
        static::deleted($forgetCurrentPrice);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effective_since' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
