<?php

namespace TahirRasheed\MediaLibrary\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use TahirRasheed\MediaLibrary\Facades\Media;
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

    public function handleMedia(array $request, string $type = 'image')
    {
        return Media::disk($this->getDisk())->handle($request, $type, $this);
    }

    public function hasMedia(string $type = 'image'): bool
    {
        $getMedia = $this->getMedia($type);

        if ($getMedia->isEmpty()) {
            return false;
        }

        return true;
    }

    public function getMedia(string $type = 'image'): Collection
    {
        if (! $this->relationLoaded('attachments')) {
            $this->load('attachments');
        }

        if (! $this->attachments) {
            return collect([]);
        }

        return $this->attachments
            ->filter(function ($item) use ($type) {
                return $item['type'] === $type;
            })
            ->sortBy('sort_order')
            ->values();
    }

    public function sizes(): array
    {
        return [];
    }

    public function collection(): string
    {
        return '';
    }
}
