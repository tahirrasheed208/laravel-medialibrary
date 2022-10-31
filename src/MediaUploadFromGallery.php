<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Traits\MediaHelper;

class MediaUploadFromGallery
{
    use MediaHelper;

    public function __construct()
    {
        $this->disk = config('medialibrary.disk_name');
    }

    public function attachGallery(array $request, string $type, Model $model)
    {
        $this->request = $request;
        $this->type = $type;
        $this->model = $model;

        $this->validateModelRegisteredConversions();

        return $this;
    }

    public function toMediaCollection(string $collection = ''): bool
    {
        $this->collection = $collection;

        if (! isset($this->request[$this->type])) {
            return false;
        }

        $files = explode(',', $this->request[$this->type]);

        if (empty($files)) {
            return false;
        }

        $this->moveTempFilesToMedia($files);

        return true;
    }

    protected function moveTempFilesToMedia(array $files)
    {
        foreach ($files as $file) {
            $temp_path = 'temp' . DIRECTORY_SEPARATOR . 'dropzone' . DIRECTORY_SEPARATOR . $file;
            $new_path = $this->getFileUploadPath() . DIRECTORY_SEPARATOR . $file;

            Storage::disk($this->disk)->move($temp_path, $new_path);

            $media = $this->model->attachments()->create([
                'type' => $this->type,
                'file_name' => $file,
                'mime_type' => Storage::disk($this->disk)->mimeType($new_path),
                'size' => Storage::disk($this->disk)->size($new_path),
                'disk' => $this->disk,
                'collection_name' => $this->getCollection(),
                'sort_order' => $this->model->attachments()->whereType($this->type)->count(),
            ]);

            $this->setDefaultConversions($media);

            $this->dispatchConversionJobs($media);
        }
    }
}
