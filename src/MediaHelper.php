<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Exceptions\FileSizeTooBigException;
use TahirRasheed\MediaLibrary\Exceptions\InvalidConversionException;
use TahirRasheed\MediaLibrary\Jobs\MediaConversion;
use TahirRasheed\MediaLibrary\Jobs\ThumbnailConversion;
use TahirRasheed\MediaLibrary\Models\Media;

class MediaHelper
{
    protected string $disk;
    protected string $collection_name = '';
    protected bool $without_conversions;
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

    public function withoutConversions(bool $value): MediaHelper
    {
        $this->without_conversions = $value;

        return $this;
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
        $conversions = [
            'thumbnail' => $media->getFilePath(),
        ];

        $media->conversions = $conversions;

        $media->save();
    }

    protected function checkMaxFileUploadSize(UploadedFile $file)
    {
        if ($file->getSize() > config('medialibrary.max_file_size')) {
            throw new FileSizeTooBigException();
        }
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
