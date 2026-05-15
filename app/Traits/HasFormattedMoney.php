<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasFormattedMoney
{
    protected function formatMoney(string $attribute): Attribute
    {
        return Attribute::make(
            get: fn () => number_format((float) $this->{$attribute}, 2, '.', '').' €',
        );
    }
}
