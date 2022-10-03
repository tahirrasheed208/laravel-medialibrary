<?php

namespace TahirRasheed\MediaLibrary\Observers;

use TahirRasheed\MediaLibrary\Models\Media;

class MediaObserver
{
    public function deleted(Media $media)
    {
        dd($media);
    }
}