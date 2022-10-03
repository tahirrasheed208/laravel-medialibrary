<?php

namespace TahirRasheed\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Observers\MediaObserver;

class Media extends Model
{
    protected $guarded = [];

    public function attachment()
    {
        return $this->morphOne(MediaAttachment::class, 'imageable');
    }

    public function deleteAllAttachments()
    {
        //
    }
}
