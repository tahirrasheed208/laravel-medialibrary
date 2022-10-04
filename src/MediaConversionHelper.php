<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Models\Media;
use Intervention\Image\Facades\Image;

class MediaConversionHelper
{
    public function generateThumbnailConversion(int $media_id): void
    {
        $thumbnail_enable = config('medialibrary.thumbnails.generate', true);

        if (! $thumbnail_enable) {
            return;
        }

        $media = Media::findOrFail($media_id);

        $this->createDirectory('thumbnail', $media->disk, $media->collection_name);

        $width = config('medialibrary.thumbnails.width', 200);
        $height = config('medialibrary.thumbnails.height', 200);

        $original_image = Storage::disk($media->disk)
            ->get($media->getFilePath());

        Image::configure(['driver' => config('medialibrary.image_driver')]);

        $image = Image::make($original_image)
            ->fit($width, $height);

        Storage::disk($media->disk)
            ->put($media->getSizePath('thumbnail'), $image->stream(), 'public');

        $this->updateConversions($media, 'thumbnail');
    }

    protected function updateConversions(Media $media, string $key): void
    {
        $conversions = $media->conversions;
        $conversions[$key] = $media->getSizePath($key);

        $media->conversions = $conversions;
        $media->save();
    }

    protected function createDirectory(string $directory, string $disk, string $collection): void
    {
        $directory = $collection . DIRECTORY_SEPARATOR . $directory;

        if (! Storage::disk($disk)->exists($directory)) {
            Storage::disk($disk)->makeDirectory($directory);
        }
    }
}
