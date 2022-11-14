<?php

namespace TahirRasheed\MediaLibrary\Conversions;

use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Models\Media;
use Intervention\Image\Facades\Image;

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

        Storage::disk($this->media->disk)
            ->put($this->media->getConversionPath($conversion->name), $image->stream(), 'public');

        $this->updateConversionsAttribute($conversion->name);
    }

    protected function resizeMedia(string $original_image, Conversion $conversion)
    {
        Image::configure(['driver' => config('medialibrary.image_driver')]);

        if ($conversion->crop) {
            return Image::make($original_image)
                ->fit($conversion->width, $conversion->height, function ($constraint) {
                    $constraint->upsize();
                }, $conversion->position);
        }

        $image = Image::make($original_image);

        if ($image->width() >= $image->height()) {
            return $image->resize($conversion->width, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        return $image->resize(null, $conversion->height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
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

        $original_image = Storage::disk($this->media->disk)
            ->get($this->media->getFilePath());

        Image::configure(['driver' => config('medialibrary.image_driver')]);

        $image = Image::make($original_image)
            ->fit($width, $height);

        Storage::disk($this->media->disk)
            ->put($this->media->getConversionPath('thumbnail'), $image->stream(), 'public');

        $this->updateConversionsAttribute('thumbnail');
    }

    protected function updateConversionsAttribute(string $key = 'original'): void
    {
        $conversions = $this->media->conversions;
        $conversions[$key] = $this->media->getConversionPath($key);

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
