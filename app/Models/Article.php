<?php

namespace App\Models;

use App\Traits\HasFormattedMoney;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFormattedMoney;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    protected function formattedPrice(): Attribute
    {
        return $this->formatMoney('price');
    }
}
