<?php

namespace TahirRasheed\MediaLibrary\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Models\MediaAttachment;

trait HasMedia
{
    public static function bootHasMedia()
    {
        static::deleting(function (Model $model) {
            $model->attachments()->delete();
        });
    }

    public function scopeMedia(Builder $query)
    {
        return $query->with('attachments');
    }

    public function attachments()
    {
        return $this->morphMany(MediaAttachment::class, 'imageable')->orderBy('sort_order');
    }
}
