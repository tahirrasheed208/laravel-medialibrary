<?php

namespace TahirRasheed\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Exceptions\SizeNotAvailableException;

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

    public function getFilePath(string $size = 'original'): string
    {
        $conversions = $this->conversions;

        if ($size === 'original') {
            return $this->getSizePath($size);
        }

        if (empty($conversions)) {
            throw new SizeNotAvailableException();
        }

        if (! isset($conversions[$size])) {
            throw new SizeNotAvailableException();
        }

        return $conversions[$size];
    }

    public function getSizePath(string $size): string
    {
        if (empty($this->collection_name)) {
            return $size . DIRECTORY_SEPARATOR . $this->file_name;
        }

        return $this->collection_name . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $this->file_name;
    }
}
