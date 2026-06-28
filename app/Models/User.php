<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name'])]
#[Hidden(['pin'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pin' => 'hashed',
            'password' => 'hashed',
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

    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class);
    }

    public function assignedWorld(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_world_id');
    }

    public function assignedVendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_vendor_id');
    }

    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn () => Cache::rememberForever(
                self::balanceCacheKey($this->id),
                function () {
                    $incoming = $this->receivedTransactions()->sum('amount');
                    $outgoing = $this->sentTransactions()->sum('amount');

                    return $incoming - $outgoing;
                }
            )
        )->shouldCache();
    }

    public static function balanceCacheKey(int $userId): string
    {
        return "user:{$userId}:balance";
    }

    public static function forgetCachedBalance(int $userId): void
    {
        Cache::forget(self::balanceCacheKey($userId));
    }

    public static function groupByFirstLetter(Collection $users)
    {
        return $users
            ->sortBy(fn ($user) => strtolower($user->name))
            ->groupBy(function ($user) {
                $first = strtoupper(substr($user->name, 0, 1));

                return ctype_alpha($first) ? $first : '*';
            })
            ->sortKeys()
            ->pipe(function ($col) {
                // Move '*' to the front if present
                if ($col->has('*')) {
                    $star = $col->pull('*');

                    return collect(['*' => $star])->merge($col);
                }

                return $col;
            });
    }
}
