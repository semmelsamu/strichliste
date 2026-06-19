<?php

namespace App\Models;

use App\Enums\SystemSound;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['system_sound', 'sound'])]
class SystemSoundSetting extends Model
{
    protected $table = 'system_sounds';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'system_sound' => SystemSound::class,
        ];
    }

    public static function get(SystemSound $systemSound): ?string
    {
        return static::where('system_sound', $systemSound)->first()?->sound;
    }
}
