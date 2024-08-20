<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Exceptions\FileMissingException;
use TahirRasheed\MediaLibrary\Traits\MediaHelper;

class MediaUpload
{
    use MediaHelper;

    public string|null $title;

    public function __construct()
    {
        $this->disk = config('medialibrary.disk_name');
    }

    public function handleMediaFromRequest(array $request, string $type, Model $model, string|null $title = null): MediaUpload
    {
        $this->request = $request;
        $this->type = $type;
        $this->model = $model;
        $this->title = $title;

        $this->validateModelRegisteredConversions();

        return $this;
    }

    public function addMediaFromRequest(array $request, string $type, Model $model, string|null $title = null): MediaUpload
    {
        $this->request = $request;
        $this->type = $type;
        $this->model = $model;
        $this->title = $title;

        $this->validateModelRegisteredConversions();

        if (! isset($request[$type])) {
            throw new FileMissingException();
        }

        return $this;
    }

    public function toMediaCollection(string $collection = '')
    {
        $this->collection = $collection;

        $this->deleteOldFileIfRequested();

        if (! isset($this->request[$this->type])) {
            return;
        }

        $file = $this->request[$this->type];

        return $this->upload($file);
    }

    public function uploadFromGallery(Model $model, string $type, UploadedFile $file, string $collection = '')
    {
        $this->model = $model;
        $this->type = $type;
        $this->collection = $collection;

        return $this->upload($file);
    }

    public function uploadFromLivewire(Model $model, string $type, $files, string|null $title = null, string $collection = '')
    {
        $this->model = $model;
        $this->type = $type;
        $this->collection = $collection;
        $this->title = $title;

        if (! is_array($files)) {
            $files[] = $files;
        }

        foreach ($files as $file) {
            $this->upload($file);
        }

        return "success";
    }

    private function upload(UploadedFile $file): array
    {
        $this->checkMaxFileUploadSize($file);

        $file->store($this->getFileUploadPath(), $this->disk);

        if (! $this->title) {
            $this->title = $file->getClientOriginalName();
        }

        $media = $this->model->attachments()->create([
            'type' => $this->type,
            'file_name' => $file->hashName(),
            'name' => $this->title,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $this->disk,
            'collection_name' => $this->getCollection(),
            'sort_order' => $this->model->attachments()->whereType($this->type)->count(),
        ]);

        $this->setDefaultConversions($media);

        $this->dispatchConversionJobs($media);

        return [
            'media_id' => $media->id,
            'file_name' => $file->hashName(),
        ];
    }

    private function deleteOldFileIfRequested(): void
    {
        if (! isset($this->request['remove_' . $this->type])) {
            return;
        }

        if ($this->request['remove_' . $this->type] === 'no') {
            return;
        }

        if (isset($this->request[$this->type])) {
            $this->checkMaxFileUploadSize($this->request[$this->type]);
        }

        $this->model->attachments()->whereType($this->type)->first()->delete();
    }
}
