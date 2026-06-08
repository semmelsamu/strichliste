<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class Sound
{
    private const DIRECTORY = 'sounds';

    /**
     * @see get(string $name)
     */
    private function __construct(
        public readonly string $name
    ) {}

    public function url()
    {
        return asset('storage/'.self::DIRECTORY.'/'.$this->name.'.mp3');
    }

    public static function all()
    {
        return collect(Storage::disk('public')->files(self::DIRECTORY))
            ->map(fn ($filename) => new Sound(
                pathinfo($filename, PATHINFO_FILENAME)
            ));
    }

    public static function get(string $name)
    {
        $result = self::all()->first(fn ($sound) => $sound->name == $name);

        if (! $result) {
            throw new NotFoundResourceException('Sound "'.$name.'" wurde nicht gefunden.');
        }

        return $result;
    }
}
