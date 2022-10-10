<?php

namespace TahirRasheed\MediaLibrary\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Exceptions\InvalidUrlmageException;

trait UploadFromUrl
{
    public function addMediaFromUrl(string $url, string $type, Model $model)
    {
        $this->validateUrlHasValidImage($url);

        $this->model = $model;

        $name = pathinfo($url, PATHINFO_BASENAME);
        $file_name = uniqid() . '_' . $name;
        $file_path = $this->getFileUploadPath() . DIRECTORY_SEPARATOR . $file_name;

        Storage::disk($this->disk)->put($file_path, file_get_contents($url), 'public');

        $media = $this->model->attachments()->create([
            'type' => $type,
            'file_name' => $file_name,
            'name' => $name,
            'mime_type' => Storage::disk($this->disk)->mimeType($file_path),
            'size' => Storage::disk($this->disk)->size($file_path),
            'disk' => $this->disk,
            'collection_name' => $this->getCollection(),
            'sort_order' => $this->model->attachments()->whereType($type)->count(),
        ]);

        $this->setDefaultConversions($media);

        $this->dispatchConversionJobs($media->id);

        return [
            'media_id' => $media->id,
            'file_name' => $file_name,
        ];
    }

    protected function validateUrlHasValidImage(string $file): bool
    {
        if (empty($file)) {
            throw InvalidUrlmageException::image();
        }

        $image = getimagesize($file);
        $mime = strtolower(substr($image['mime'], 0, 5));

        if ($mime !== 'image') {
            throw InvalidUrlmageException::image();
        }

        return true;
    }
}
