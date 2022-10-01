<?php

namespace TahirRasheed\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $guarded = [];

    public function attachment()
    {
        return $this->morphOne(MediaAttachment::class, 'imageable');
    }
}
