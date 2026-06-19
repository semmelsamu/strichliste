<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class Article extends Model
{
    use SoftDeletes;

    private const IMAGE_DIRECTORY = 'article-images';

    protected $casts = [
        'sounds' => 'array',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ArticlePrice::class);
    }

    protected function currentPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->prices()->latest('effective_since')->value('price')
        )->shouldCache();
    }

    protected function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class);
    }

    private function imagePath(): string
    {
        return self::IMAGE_DIRECTORY.'/'.$this->id.'.jpg';
    }

    public function hasImage(): bool
    {
        return Storage::disk('public')->exists($this->imagePath());
    }

    public function imageUrl(): ?string
    {
        return $this->hasImage() ? asset('storage/'.$this->imagePath()) : null;
    }

    public function setImageFromUpload(UploadedFile $file): void
    {
        $encoded = ImageManager::usingDriver(Driver::class)
            ->decodeSplFileInfo($file)
            ->scaleDown(800, 800)
            ->encode(new JpegEncoder(quality: 70));

        Storage::disk('public')->put($this->imagePath(), (string) $encoded);
    }

    public function deleteImage(): void
    {
        Storage::disk('public')->delete($this->imagePath());
    }
}
