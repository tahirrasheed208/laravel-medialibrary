<?php

namespace TahirRasheed\MediaLibrary\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use TahirRasheed\MediaLibrary\Conversions\ConversionHelper;

class ThumbnailConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $media_id;

    public function __construct(int $media_id)
    {
        $this->onQueue(config('medialibrary.queue_name'));

        $this->media_id = $media_id;
    }

    public function handle()
    {
        (new ConversionHelper)->generateThumbnailConversion($this->media_id);
    }
}
