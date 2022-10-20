<?php

namespace TahirRasheed\MediaLibrary\Media;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use TahirRasheed\MediaLibrary\Models\Media;
use TahirRasheed\MediaLibrary\Jobs\MediaConversion;
use TahirRasheed\MediaLibrary\Jobs\ThumbnailConversion;
use TahirRasheed\MediaLibrary\Exceptions\FileMissingException;
use TahirRasheed\MediaLibrary\Exceptions\InvalidConversionException;

class FileAdder
{
    protected string $type;
    protected string $collection;
    protected string $disk;
    public bool $without_conversions = false;
    protected array $request;
    protected $model;

    public function __construct()
    {
        $this->disk = config('medialibrary.disk_name');
    }

    public function withoutConversions(bool $value)
    {
        $this->without_conversions = $value;

        return $this;
    }

    public function useDisk(string $disk): FileAdder
    {
        $this->disk = $disk;

        return $this;
    }

    public function toMediaCollection(string $collection = null)
    {
        $this->collection = $collection;

        $this->deleteOldFileIfRequested();

        if (! isset($this->request[$this->type])) {
            return;
        }

        $file = $this->request[$this->type];

        return $this->upload($file);
    }

    public function handleMediaFromRequest(array $request, string $type, Model $model): FileAdder
    {
        $this->request = $request;
        $this->type = $type;
        $this->model = $model;

        $this->validateModelRegisteredConversions();

        return $this;
    }

    public function addMediaFromRequest(array $request, string $type, Model $model): FileAdder
    {
        $this->request = $request;
        $this->type = $type;
        $this->model = $model;

        $this->validateModelRegisteredConversions();

        if (! isset($request[$type])) {
            throw new FileMissingException();
        }

        return $this;
    }

    private function upload(UploadedFile $file): array
    {
        $this->checkMaxFileUploadSize($file);

        $file->store($this->getFileUploadPath(), $this->disk);

        $media = $this->model->attachments()->create([
            'type' => $this->type,
            'file_name' => $file->hashName(),
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $this->disk,
            'collection_name' => $this->getCollection(),
            'sort_order' => $this->model->attachments()->whereType($this->type)->count(),
        ]);

        $this->setDefaultConversions($media);

        $this->dispatchConversionJobs($media->id);

        return [
            'media_id' => $media->id,
            'file_name' => $file->hashName(),
        ];
    }

    private function validateModelRegisteredConversions(): void
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

    private function dispatchConversionJobs(int $media_id)
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

    private function setDefaultConversions(Media $media)
    {
        $conversions = [
            'thumbnail' => $media->getFilePath(),
        ];

        $media->conversions = $conversions;

        $media->save();
    }

    private function checkMaxFileUploadSize(UploadedFile $file)
    {
        if ($file->getSize() > config('medialibrary.max_file_size')) {
            throw new FileSizeTooBigException();
        }
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

    private function getFileUploadPath(): string
    {
        return $this->getCollection() . DIRECTORY_SEPARATOR . 'original';
    }

    private function getCollection(): string
    {
        if ($this->collection) {
            return $this->collection;
        }

        return $this->getCollectionFromModel();
    }

    private function getCollectionFromModel(): string
    {
        if (! $this->model) {
            return '';
        }

        $collection = $this->model->defaultCollection();

        return Str::kebab($collection);
    }
}
