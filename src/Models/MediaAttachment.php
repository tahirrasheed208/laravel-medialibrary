<?php

namespace TahirRasheed\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;

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
}
