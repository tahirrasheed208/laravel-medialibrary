<?php

namespace TahirRasheed\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
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

    public function getFilePath(string $key = 'original'): string
    {
        $conversions = $this->conversions;

        if ($key === 'original') {
            return $this->getConversionPath($key);
        }

        if (empty($conversions)) {
            throw new ConversionNotAvailableException();
        }

        if (! isset($conversions[$key])) {
            throw new ConversionNotAvailableException();
        }

        return $conversions[$key];
    }

    public function getConversionPath(string $key): string
    {
        if (empty($this->collection_name)) {
            return $key . DIRECTORY_SEPARATOR . $this->file_name;
        }

        return $this->collection_name . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . $this->file_name;
    }
}
