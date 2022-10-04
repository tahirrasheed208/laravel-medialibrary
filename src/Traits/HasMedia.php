<?php

namespace TahirRasheed\MediaLibrary\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Facades\Media;
use TahirRasheed\MediaLibrary\Models\Media as MediaModel;

trait HasMedia
{
    protected string $collection = '';
    protected ?string $disk = null;

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
        return $this->morphMany(MediaModel::class, 'imageable')->orderBy('sort_order');
    }

    public function toMediaCollection(string $collection): Model
    {
        $this->collection = $collection;

        return $this;
    }

    public function useDisk(string $disk): Model
    {
        $this->disk = $disk;

        return $this;
    }

    public function handleMedia(array $request, string $type = 'image')
    {
        return Media::disk($this->disk)
            ->collection($this->collection)
            ->handle($request, $type, $this);
    }

    public function hasMedia(string $type = 'image'): bool
    {
        $media = $this->getMedia($type);

        if (! $media) {
            return false;
        }

        return true;
    }

    public function getMedia(string $type = 'image'): ?MediaModel
    {
        if (! $this->relationLoaded('attachments')) {
            $this->load('attachments');
        }

        return $this->attachments
            ->first(function ($item) use ($type) {
                return $item['type'] === $type;
            });
    }

    public function getFirstMediaUrl(string $type = 'image', string $size = 'original'): string
    {
        $media = $this->getMedia($type);

        if (! $media) {
            return '';
        }

        return Storage::disk($media->disk)->url($media->getFilePath($size));
    }

    public function getThumbnailUrl(string $type = 'image'): string
    {
        $media = $this->getMedia($type);

        if (! $media) {
            return '';
        }

        try {
            return $media->getFilePath('thumbnail');
        } catch (\Throwable $th) {
            return $media->getFilePath();
        }
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
