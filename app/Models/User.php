<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name'])]
#[Hidden(['pin'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => UserType::class,
            'pin' => 'hashed',
        ];
    }

    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_user_id');
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_user_id')
            ->orWhere('to_user_id', $this->id);
    }

    protected function balance(): Attribute
    {
        return Attribute::make(
            get: function () {
                $incoming = $this->receivedTransactions()->sum('amount');
                $outgoing = $this->sentTransactions()->sum('amount');

                return $incoming - $outgoing;
            }
        );
    }

    public static function groupByFirstLetter(Collection $users)
    {
        return $users
            ->groupBy(function ($user) {
                $first = strtoupper(substr($user->name, 0, 1));

                return ctype_alpha($first) ? $first : '*';
            })
            ->sortKeys()
            ->pipe(function ($col) {
                // Move '*' to the end if present
                if ($col->has('*')) {
                    $star = $col->pull('*');
                    $col->put('*', $star);
                }

                return $col;
            });
    }
}
