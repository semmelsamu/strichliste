<?php

namespace App\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class Sound
{
    private const DIRECTORY = 'sounds';

    /**
     * @see get(string $name)
     */
    private function __construct(
        public readonly string $filename
    ) {}

    public function name()
    {
        return pathinfo($this->filename, PATHINFO_FILENAME);
    }

    public function url()
    {
        return asset('storage/'.self::DIRECTORY.'/'.$this->filename);
    }

    public static function all()
    {
        return collect(Storage::disk('public')->files(self::DIRECTORY))
            ->map(fn ($filename) => new Sound(
                basename($filename)
            ));
    }

    public static function get(string $name)
    {
        $result = self::all()->first(fn ($sound) => $sound->name() == $name);

        if (! $result) {
            throw new NotFoundResourceException('Sound "'.$name.'" wurde nicht gefunden.');
        }

        return $result;
    }

    public static function create(UploadedFile $file)
    {
        $originalName = $file->getClientOriginalName();

        $storedName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME))
            .'.'
            .pathinfo($originalName, PATHINFO_EXTENSION);

        return $file->storeAs(self::DIRECTORY, $storedName, 'public');
    }

    public function destroy()
    {
        Storage::disk('public')->delete(self::DIRECTORY.'/'.$this->filename);
    }
}
