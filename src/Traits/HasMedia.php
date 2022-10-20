<?php

namespace TahirRasheed\MediaLibrary\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Conversions\Conversion;
use TahirRasheed\MediaLibrary\MediaUpload;
use TahirRasheed\MediaLibrary\MediaUploadFromGallery;
use TahirRasheed\MediaLibrary\MediaUploadFromUrl;
use TahirRasheed\MediaLibrary\Models\Media;

trait HasMedia
{
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

    public function addMedia(UploadedFile $file, string $type = 'image'): MediaUpload
    {
        $request = [$type => $file];

        return (new MediaUpload)->addMediaFromRequest($request, $type, $this);
    }

    public function addMediaFromRequest(string $type = 'image'): MediaUpload
    {
        return (new MediaUpload)->addMediaFromRequest(request()->toArray(), $type, $this);
    }

    public function handleMediaFromRequest(string $type = 'image'): MediaUpload
    {
        return (new MediaUpload)->handleMediaFromRequest(request()->toArray(), $type, $this);
    }

    public function attachGalleryToModelFromRequest(string $type = 'gallery'): MediaUploadFromGallery
    {
        return (new MediaUploadFromGallery)->attachGallery(request()->toArray(), $type, $this);
    }

    public function addMediaFromUrl(string $url, string $type = 'image'): MediaUploadFromUrl
    {
        return (new MediaUploadFromUrl)->addMediaFromUrl($url, $type, $this);
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
