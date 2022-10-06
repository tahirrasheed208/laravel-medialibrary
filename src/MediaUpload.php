<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Exceptions\InvalidConversionException;
use TahirRasheed\MediaLibrary\Jobs\MediaConversion;
use TahirRasheed\MediaLibrary\Jobs\ThumbnailConversion;
use TahirRasheed\MediaLibrary\Traits\MediaHelper;

class MediaUpload
{
    use MediaHelper;

    public function __construct()
    {
        $this->disk = config('medialibrary.disk_name');
    }

    public function handle(array $request, string $type, ?Model $model = null): bool
    {
        $this->model = $model;

        $this->validateModelRegisteredConversions();

        $this->deleteOldFileIfRequested($request, $type);

        if (! isset($request[$type])) {
            return false;
        }

        $this->upload($request[$type], $type);

        return true;
    }

    public function upload(UploadedFile $file, string $type): array
    {
        $this->checkMaxFileUploadSize($file);

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

        $this->dispatchConversionJobs($media->id);

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

        if (isset($request[$type])) {
            $this->checkMaxFileUploadSize($request[$type]);
        }

        $this->model->attachments()->whereType($type)->first()->delete();
    }

    protected function dispatchConversionJobs(int $media_id)
    {
        ThumbnailConversion::dispatch($media_id);

        if ($this->without_conversions) {
            return;
        }

        if (empty($this->model->mediaConversions)) {
            return;
        }

        MediaConversion::dispatch($media_id, $this->model->mediaConversions);
    }

    protected function validateModelRegisteredConversions(): void
    {
        if ($this->without_conversions) {
            return;
        }

        $this->model->registerMediaConversions();

        if (empty($this->model->mediaConversions)) {
            return;
        }

        foreach ($this->model->mediaConversions as $conversion) {
            if (! property_exists($conversion, 'width')) {
                throw InvalidConversionException::width();
            }

            if (! property_exists($conversion, 'height')) {
                throw InvalidConversionException::height();
            }
        }
    }
}