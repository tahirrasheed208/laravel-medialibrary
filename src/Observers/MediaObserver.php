<?php

namespace TahirRasheed\MediaLibrary\Observers;

use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Models\Media;

class MediaObserver
{
    public function deleted(Media $media)
    {
        Storage::disk($media->disk)->delete($media->getConversionPath('original'));

        $this->decrementSortOrder($media);

        if (empty($media->conversions)) {
            return;
        }

        foreach ($media->conversions as $conversion) {
            Storage::disk($media->disk)->delete($conversion);
        }
    }

    protected function decrementSortOrder(Media $media)
    {
        $imageable = $media->imageable;

        if (! $imageable) {
            return;
        }

        $imageable->attachments()
            ->whereType($media->type)
            ->where('sort_order', '>', $media->sort_order)
            ->decrement('sort_order');
    }
}