<?php

namespace TahirRasheed\MediaLibrary\Conversions;

use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Models\Media;
use Intervention\Image\Laravel\Facades\Image;
use TahirRasheed\MediaLibrary\Jobs\MediaConversion;
use TahirRasheed\MediaLibrary\Jobs\ThumbnailConversion;

class ConversionHelper
{
    protected Media $media;

    public function conversions(int $media_id, array $conversions)
    {
        $this->media = Media::findOrFail($media_id);

        foreach ($conversions as $conversion) {
            $this->generateConversion($conversion);
        }
    }

    public function generateConversion(Conversion $conversion)
    {
        $this->createDirectory($conversion->name);

        $original_image = Storage::disk($this->media->disk)
            ->get($this->media->getFilePath());

        $image = $this->resizeMedia($original_image, $conversion);

        $webp_conversion = config('medialibrary.webp_conversion');
        $webp_quality = config('medialibrary.webp_quality') ?: 75;

        if ($webp_conversion) {
            $image = $image->toWebp($webp_quality);
        } else {
            $image = $image->encodeByMediaType();
        }

        Storage::disk($this->media->disk)
            ->put($this->media->getConversionPath($conversion->name, $webp_conversion), $image->toFilePointer(), 'public');

        $this->updateConversionsAttribute($conversion->name, $webp_conversion);
    }

    protected function resizeMedia(string $original_image, Conversion $conversion)
    {
        if ($conversion->crop) {
            return Image::read($original_image)
                ->resizeDown($conversion->width, $conversion->height, $conversion->position);
        }

        $image = Image::read($original_image);

        if ($image->width() >= $image->height()) {
            return $image->scaleDown($conversion->width);
        }

        return $image->scaleDown(height: $conversion->height);
    }

    public function convertOriginalImageToWebp(int $media_id, array $media_conversions = []): void
    {
        $this->media = Media::findOrFail($media_id);
        $original_image_path = $this->media->getFilePath();

        $original_image = Storage::disk($this->media->disk)
            ->get($original_image_path);

        $webp_path = $this->media->getConversionPath('original', true);

        $webp_quality = config('medialibrary.webp_quality') ?: 75;
        $webp_quality = $webp_quality == 100 ? 99 : $webp_quality;

        $image = Image::read($original_image)->toWebp($webp_quality);

        Storage::disk($this->media->disk)
            ->put($webp_path, $image->toFilePointer(), 'public');

        $this->updateConversionsAttribute('original', true);

        // generate conversions
        ThumbnailConversion::dispatch($media_id);

        if (!empty($media_conversions)) {
            MediaConversion::dispatch($media_id, $media_conversions);
        }
    }

    public function generateThumbnailConversion(int $media_id): void
    {
        $thumbnail_enable = config('medialibrary.thumbnails.generate', true);

        if (! $thumbnail_enable) {
            return;
        }

        $this->media = Media::findOrFail($media_id);

        $this->createDirectory('thumbnail');

        $width = config('medialibrary.thumbnails.width', 200);
        $height = config('medialibrary.thumbnails.height', 200);

        $webp_conversion = config('medialibrary.webp_conversion');
        $webp_quality = config('medialibrary.webp_quality') ?: 75;

        $original_image = Storage::disk($this->media->disk)
            ->get($this->media->getFilePath());

        $image = Image::read($original_image)
            ->cover($width, $height);

        if ($webp_conversion) {
            $image = $image->toWebp($webp_quality);
        } else {
            $image = $image->encodeByMediaType();
        }

        Storage::disk($this->media->disk)
            ->put($this->media->getConversionPath('thumbnail', $webp_conversion), $image->toFilePointer(), 'public');

        $this->updateConversionsAttribute('thumbnail', $webp_conversion);
    }

    protected function updateConversionsAttribute(string $key = 'original', bool $webp = false): void
    {
        $conversions = $this->media->conversions;
        $conversions[$key] = $this->media->getConversionPath($key, $webp);

        $this->media->conversions = $conversions;
        $this->media->save();
    }

    protected function createDirectory(string $directory): void
    {
        $directory = $this->media->collection_name . DIRECTORY_SEPARATOR . $directory;

        if (! Storage::disk($this->media->disk)->exists($directory)) {
            Storage::disk($this->media->disk)->makeDirectory($directory);
        }
    }
}
