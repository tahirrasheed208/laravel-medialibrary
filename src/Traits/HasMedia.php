<?php

namespace TahirRasheed\MediaLibrary\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Facades\Media;
use TahirRasheed\MediaLibrary\Models\MediaAttachment;

trait HasMedia
{
    protected string $collection = '';

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

    public function toMediaCollection(string $collection): Model
    {
        $this->collection = $collection;

        return $this;
    }

    public function handleMedia(array $request, string $type = 'image')
    {
        return Media::collection($this->collection)->handle($request, $type, $this);
    }

    public function hasMedia(string $type = 'image'): bool
    {
        $get_media = $this->getMedia($type);

        if ($get_media->isEmpty()) {
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

    public function getFirstMediaUrl(string $type = 'image', string $size = 'original'): string
    {
        $get_media = $this->getMedia($type);

        if ($get_media->isEmpty()) {
            return 'placeholder.png';
        }

        $media = $get_media->first()->media;
        $file_path = $get_media->first()->getFilePath($size);

        return Storage::disk($media->disk)->url('original/' . $media->file_name);
    }

    public function sizes(): array
    {
        return [];
    }

    public function defaultCollection(): string
    {
        return '';
    }
}
