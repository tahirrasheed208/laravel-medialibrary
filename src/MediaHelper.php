<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use TahirRasheed\MediaLibrary\Models\Media;

class MediaHelper
{
    protected string $disk = config('filesystems.default');
    protected string $collection_name = '';
    protected $model;

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
            'collection_name' => $this->collection_name,
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

        $model->delete();
    }

    protected function getFileUploadPath(): string
    {
        $collection = $this->collection_name;

        if (! $this->collection_name) {
            $collection = $this->getCollectionFromModel();
        }

        return $collection . DIRECTORY_SEPARATOR . 'original';
    }

    protected function getCollectionFromModel(): string
    {
        if (! $this->model) {
            return '';
        }

        $collection = $this->model->collection();

        return Str::kebab($collection);
    }
}
