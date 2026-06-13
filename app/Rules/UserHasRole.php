<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UserHasRole implements ValidationRule
{
    public function __construct(private string $role) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = User::whereKey($value)
            ->role($this->role) // Spatie scope
            ->exists();

        if (! $exists) {
            $fail("The selected $attribute does not have the required role.");
        }
    }
}
