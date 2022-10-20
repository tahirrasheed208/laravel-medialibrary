<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Http\UploadedFile;
use TahirRasheed\MediaLibrary\Jobs\ThumbnailConversion;
use TahirRasheed\MediaLibrary\Models\Media;
use TahirRasheed\MediaLibrary\Traits\MediaHelper;

class MediaUploadForSetting
{
    use MediaHelper;

    public function __construct()
    {
        $this->disk = config('medialibrary.disk_name');
    }

    public function toMediaCollection(string $collection = '')
    {
        $this->collection = $collection;

        return $this;
    }

    public function handle(array $request, string $type, string $option_name)
    {
        $this->deleteOldFileIfRequested($request, $type, $option_name);

        if (! isset($request[$type])) {
            return null;
        }

        $media = $this->upload($request[$type], $type);

        return $media['media_id'];
    }

    public function upload(UploadedFile $file, string $type): array
    {
        $this->checkMaxFileUploadSize($file);

        $file->store($this->getFileUploadPath(), $this->disk);

        $media = Media::create([
            'type' => $type,
            'file_name' => $file->hashName(),
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $this->disk,
            'collection_name' => $this->getCollection(),
        ]);

        $this->setDefaultConversions($media);

        ThumbnailConversion::dispatch($media->id);

        return [
            'media_id' => $media->id,
            'file_name' => $file->hashName(),
        ];
    }

    protected function deleteOldFileIfRequested(array $request, string $type, string $option_name): void
    {
        if (! isset($request['remove_' . $type])) {
            return;
        }

        if ($request['remove_' . $type] === 'no') {
            return;
        }

        if (isset($request[$type])) {
            $this->checkMaxFileUploadSize($request[$type]);
        }

        $media = Media::find(setting()->get($option_name));

        if ($media) {
            $media->delete();
        }
    }
}
