<?php

namespace TahirRasheed\MediaLibrary\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Conversions\Conversion;
use TahirRasheed\MediaLibrary\MediaUpload;
use TahirRasheed\MediaLibrary\Models\Media;

trait HasMedia
{
    protected string $collection = '';
    protected ?string $disk = null;
    protected bool $without_conversions = false;
    public array $mediaConversions = [];

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
        return $this->morphMany(Media::class, 'imageable')->orderBy('sort_order');
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

    public function withoutConversions(): Model
    {
        $this->without_conversions = true;

        return $this;
    }

    public function handleMedia(array $request, string $type = 'image')
    {
        return (new MediaUpload)->disk($this->disk)
            ->collection($this->collection)
            ->withoutConversions($this->without_conversions)
            ->handle($request, $type, $this);
    }

    public function attachGallery(array $request, string $type = 'gallery')
    {
        return (new MediaUpload)->collection($this->collection)
            ->withoutConversions($this->without_conversions)
            ->attachGallery($request, $type, $this);
    }

    public function addMediaFromUrl(string $url, string $type = 'image')
    {
        return (new MediaUpload)->disk($this->disk)
            ->collection($this->collection)
            ->withoutConversions($this->without_conversions)
            ->addMediaFromUrl($url, $type, $this);
    }

    public function hasMedia(string $type = 'image'): bool
    {
        $media = $this->getMedia($type);

        if (! $media) {
            return false;
        }

        return true;
    }

    public function getMedia(string $type = 'image'): ?Media
    {
        if (! $this->relationLoaded('attachments')) {
            $this->load('attachments');
        }

        return $this->attachments
            ->first(function ($item) use ($type) {
                return $item['type'] === $type;
            });
    }

    public function getAttachments(string $type = 'gallery')
    {
        if (! $this->relationLoaded('attachments')) {
            $this->load('attachments');
        }

        return $this->attachments
            ->filter(function ($item) use ($type) {
                return $item['type'] === $type;
            });
    }

    public function getFirstMediaUrl(string $type = 'image', string $conversion = 'original'): string
    {
        $media = $this->getMedia($type);

        if (! $media) {
            return '';
        }

        return Storage::disk($media->disk)->url($media->getFilePath($conversion));
    }

    public function getThumbnailUrl(string $type = 'image'): string
    {
        $media = $this->getMedia($type);

        if (! $media) {
            return '';
        }

        try {
            return Storage::disk($media->disk)->url($media->getFilePath('thumbnail'));
        } catch (\Throwable $th) {
            return $this->getFirstMediaUrl();
        }
    }

    public function addMediaConversion(string $name): Conversion
    {
        $conversion = (new Conversion)->create($name);

        $this->mediaConversions[] = $conversion;

        return $conversion;
    }

    public function registerMediaConversions()
    {
    }

    public function defaultCollection(): string
    {
        return '';
    }
}
