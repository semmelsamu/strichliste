<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    public function prices(): HasMany
    {
        return $this->hasMany(ArticlePrice::class);
    }

    protected function currentPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->prices()->latest('effective_since')->value('price')
        );
    }

    protected function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
