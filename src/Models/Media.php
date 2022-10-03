<?php

namespace TahirRasheed\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Observers\MediaObserver;

class Media extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        self::observe(MediaObserver::class);
    }

    public function attachment()
    {
        return $this->morphOne(MediaAttachment::class, 'imageable');
    }
}
