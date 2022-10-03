<?php

namespace TahirRasheed\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Exceptions\SizeNotAvailableException;

class MediaAttachment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'conversions' => 'array',
    ];

    protected $with = ['media'];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function getFilePath(string $size): string
    {
        $collection = $this->media->collection_name;
        $conversions = $this->conversions;

        if ($size === 'original') {
            //
        }

        if (empty($conversions)) {
            throw new SizeNotAvailableException();
        }

        if (! isset($conversions[$size])) {
            throw new SizeNotAvailableException();
        }

        return '';
    }
}
