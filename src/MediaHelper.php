<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use TahirRasheed\MediaLibrary\Models\Media;

class MediaHelper
{
    protected string $disk = config('filesystems.default');
    protected string $collection_name;

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
        $this->deleteOldFileIfRequested($request, $type, $model);

        if (! isset($request[$type])) {
            return false;
        }

        $uploaded_file = $this->upload($request[$type]);

        if (! $model) {
            return true;
        }

        $model->attachments()->create([
            'media_id' => $uploaded_file['media_id'],
            'type' => $type,
            'sort_order' => $model->attachments()->whereType($type)->count() + 1,
        ]);

        return true;
    }

    public function upload(UploadedFile $file): array
    {
        $file->store($this->getCollectionUploadPath(), $this->disk);

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

    protected function deleteOldFileIfRequested(array $request, string $type, ?Model $model = null): void
    {
        if (! isset($request['remove_' . $type])) {
            return;
        }

        if ($request['remove_' . $type] === 'no') {
            return;
        }

        $attachment = $model->attachments()->whereType($type)->first();

        $this->delete($attachment->media);
    }

    public function delete(?Model $model = null): void
    {
        if (! $model) {
            return;
        }

        $model->delete();
    }

    protected function getCollectionUploadPath(): string
    {
        $collection = '';

        if ($this->collection_name) {
            $collection = $this->collection_name;
        }

        return $collection . DIRECTORY_SEPARATOR . 'original';
    }
}
