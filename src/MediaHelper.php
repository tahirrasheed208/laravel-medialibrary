<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use TahirRasheed\MediaLibrary\Models\Media;

class MediaHelper
{
    protected string $disk = config('filesystems.default');

    public function disk(string $disk): MediaHelper
    {
        $this->disk = $disk;

        return $this;
    }

    public function upload(UploadedFile $file): array
    {
        $file->store('original', $this->disk);

        $media = Media::create([
            'file_name' => $file->hashName(),
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $this->disk,
        ]);

        return [
            'media_id' => $media->id,
            'file_name' => $file->hashName(),
        ];
    }
}
