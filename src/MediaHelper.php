<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Models\Media;

class MediaHelper
{
    protected string $disk;
    protected string $collection_name = '';
    protected $model;

    public function __construct()
    {
        $this->disk = config('filesystems.default');
    }

    public function disk(string $disk): MediaHelper
    {
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

        $uploaded_file = $this->upload($request[$type]);

        if (! $this->model) {
            return true;
        }

        $this->model->attachments()->create([
            'media_id' => $uploaded_file['media_id'],
            'type' => $type,
            'sort_order' => $model->attachments()->whereType($type)->count() + 1,
        ]);

        return true;
    }

    public function upload(UploadedFile $file): array
    {
        $file->store($this->getFileUploadPath(), $this->disk);

        $media = Media::create([
            'file_name' => $file->hashName(),
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $this->disk,
            'collection_name' => $this->getCollectionFromModel(),
        ]);

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

        $attachment = $this->model->attachments()->whereType($type)->first();

        $this->delete($attachment->media);
    }

    public function delete(?Model $model = null): void
    {
        if (! $model) {
            return;
        }

        $model->deleteAllAttachments();
    }

    protected function getFileUploadPath(): string
    {
        $collection = $this->collection_name;

        if (empty($this->collection_name)) {
            $collection = $this->getCollectionFromModel();
        }

        return $collection . DIRECTORY_SEPARATOR . 'original';
    }

    protected function getCollectionFromModel(): string
    {
        if (! $this->model) {
            return '';
        }

        $collection = $this->model->defaultCollection();

        return Str::kebab($collection);
    }
}
