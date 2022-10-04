<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Jobs\MediaConversion;
use TahirRasheed\MediaLibrary\Models\Media;

class MediaHelper
{
    protected string $disk;
    protected string $collection_name = '';
    protected $model;

    public function __construct()
    {
        $this->disk = config('medialibrary.disk_name');
    }

    public function disk(string $disk = null): MediaHelper
    {
        if (! $disk) {
            return $this;
        }

        $this->disk = $disk;

        return $this;
    }

    public function collection(string $collection_name): MediaHelper
    {
        $this->collection_name = $collection_name;

        return $this;
    }

    public function handle(array $request, string $type, ?Model $model = null): bool
    {
        $this->model = $model;

        $this->deleteOldFileIfRequested($request, $type);

        if (! isset($request[$type])) {
            return false;
        }

        $this->upload($request[$type], $type);

        return true;
    }

    public function upload(UploadedFile $file, string $type): array
    {
        $file->store($this->getFileUploadPath(), $this->disk);

        $media = $this->model->attachments()->create([
            'type' => $type,
            'file_name' => $file->hashName(),
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $this->disk,
            'collection_name' => $this->getCollection(),
            'sort_order' => $this->model->attachments()->whereType($type)->count(),
        ]);

        $this->setDefaultConversions($media);

        MediaConversion::dispatch($media->id);

        return [
            'media_id' => $media->id,
            'file_name' => $file->hashName(),
        ];
    }

    protected function deleteOldFileIfRequested(array $request, string $type): void
    {
        if (! isset($request['remove_' . $type])) {
            return;
        }

        if ($request['remove_' . $type] === 'no') {
            return;
        }

        $this->model->attachments()->whereType($type)->first()->delete();
    }

    protected function getFileUploadPath(): string
    {
        return $this->getCollection() . DIRECTORY_SEPARATOR . 'original';
    }

    protected function getCollection(): string
    {
        if ($this->collection_name) {
            return $this->collection_name;
        }

        return $this->getCollectionFromModel();
    }

    protected function getCollectionFromModel(): string
    {
        if (! $this->model) {
            return '';
        }

        $collection = $this->model->defaultCollection();

        return Str::kebab($collection);
    }

    protected function setDefaultConversions(Media $media)
    {
        $file_path = $media->getFilePath();

        $conversions = [
            'thumbnail' => $file_path,
        ];

        foreach ($media->imageable->sizes() as $size) {
            $conversions[$size] = $file_path;
        }

        $media->conversions = $conversions;
        $media->save();
    }
}
