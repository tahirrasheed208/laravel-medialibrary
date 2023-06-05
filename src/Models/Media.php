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

    public function getUrl(string $conversion = 'original')
    {
        return Storage::disk($this->disk)->url($this->getFilePath($conversion));
    }

    public function getFilePath(string $key = 'original'): string
    {
        $conversions = $this->conversions;

        if ($key === 'original') {
            return $this->getConversionPath($key);
        }

        $environment = config('app.env');
        $conversionNotAvailable = empty($conversions) || ! isset($conversions[$key]);

        if ($environment === 'production' && $conversionNotAvailable) {
            return $this->getConversionPath();
        }

        if ($conversionNotAvailable) {
            throw new ConversionNotAvailableException();
        }

        return $conversions[$key];
    }

    public function getConversionPath(string $key = 'original'): string
    {
        if (empty($this->collection_name)) {
            return "{$key}/{$this->file_name}";
        }

        return "{$this->collection_name}/{$key}/{$this->file_name}";
    }
}
