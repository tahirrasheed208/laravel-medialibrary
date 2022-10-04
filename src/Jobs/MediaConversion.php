<?php

namespace TahirRasheed\MediaLibrary\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use TahirRasheed\MediaLibrary\MediaConversionHelper;

class MediaConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $media_id;
    protected bool $without_conversions;

    public function __construct(int $media_id, bool $without_conversions)
    {
        $this->onQueue(config('medialibrary.queue_name'));

        $this->media_id = $media_id;
        $this->without_conversions = $without_conversions;
    }

    public function handle()
    {
        (new MediaConversionHelper)->generateThumbnailConversion($this->media_id);

        if (! $this->without_conversions) {
            // generate conversions
        }
    }
}
