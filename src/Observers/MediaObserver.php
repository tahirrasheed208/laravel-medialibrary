<?php

namespace TahirRasheed\MediaLibrary\Observers;

use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Models\Media;

class MediaObserver
{
    public function deleted(Media $media)
    {
        Storage::disk($media->disk)->delete($media->getSizePath('original'));

        if (empty($media->conversions)) {
            return;
        }

        foreach ($media->conversions as $conversion) {
            Storage::disk($media->disk)->delete($media->getSizePath($conversion));
        }
    }
}