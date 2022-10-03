<?php

namespace TahirRasheed\MediaLibrary\Observers;

use TahirRasheed\MediaLibrary\Models\Media;

class MediaObserver
{
    public function deleted(Media $media)
    {
        // dump($media->attachment);
        // dd($media);
    }
}