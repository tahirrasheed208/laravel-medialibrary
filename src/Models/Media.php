<?php

namespace TahirRasheed\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Exceptions\ConversionNotAvailableException;

class Media extends Model
{
    protected $guarded = [];

    protected $casts = [
        'conversions' => 'array',
    ];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function getTitle()
    {
        return $this->name;
    }

    public function getUrl(string $conversion = 'original')
    {
        return Storage::disk($this->disk)->url($this->getFilePath($conversion));
    }

    public function getFilePath(string $key = 'original'): string
    {
        $conversions = $this->conversions;

        if ($key === 'original') {
            return isset($conversions['original']) ? $conversions['original'] : $this->getConversionPath();
        }

        $conversionNotAvailable = empty($conversions) || ! isset($conversions[$key]);

        if ($conversionNotAvailable && config('medialibrary.conversion_missing_exception')) {
            throw new ConversionNotAvailableException();
        }

        if ($conversionNotAvailable) {
            return isset($conversions['original']) ? $conversions['original'] : $this->getConversionPath();
        }

        return $conversions[$key];
    }

    public function getConversionPath(string $key = 'original', bool $webp = false): string
    {
        $filename = $this->getFileNameForExtension($webp);

        if (empty($this->collection_name)) {
            return "{$key}/{$filename}";
        }

        return "{$this->collection_name}/{$key}/{$filename}";
    }

    public function getOriginalFileUrl(): string
    {
        return Storage::disk($this->disk)->url($this->getOriginalFilePath());
    }

    public function getOriginalFilePath(): string
    {
        if (empty($this->collection_name)) {
            return "original/{$this->file_name}";
        }

        return "{$this->collection_name}/original/{$this->file_name}";
    }

    public function toArray()
    {
        $attributes = parent::toArray();

        $attributes['url'] = $this->getUrl();
        $attributes['thumbnail_url'] = $this->getUrl('thumbnail');

        return $attributes;
    }

    public function isImage()
    {
        $types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        return in_array($this->mime_type, $types);
    }

    protected function getFileNameForExtension(bool $webp = false)
    {
        if (! $webp) {
            return $this->file_name;
        }

        $filename = pathinfo($this->file_name, PATHINFO_FILENAME);

        return "{$filename}.webp";
    }
}
