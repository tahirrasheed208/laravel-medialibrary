<?php

namespace TahirRasheed\MediaLibrary\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use TahirRasheed\MediaLibrary\Exceptions\FileSizeTooBigException;
use TahirRasheed\MediaLibrary\Exceptions\InvalidConversionException;
use TahirRasheed\MediaLibrary\Jobs\MediaConversion;
use TahirRasheed\MediaLibrary\Jobs\ThumbnailConversion;
use TahirRasheed\MediaLibrary\Jobs\WebpConversion;
use TahirRasheed\MediaLibrary\Models\Media;

trait MediaHelper
{
    public string $type;
    public string $collection;
    public string $disk;
    public bool $without_conversions = false;
    public array $request;
    public $model;

    public function useDisk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public function withoutConversions(bool $value)
    {
        $this->without_conversions = $value;

        return $this;
    }

    protected function getFileUploadPath(): string
    {
        return $this->getCollection() . DIRECTORY_SEPARATOR . 'original';
    }

    protected function getCollection(): string
    {
        if ($this->collection) {
            return $this->collection;
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

    protected function dispatchConversionJobs(Media $media)
    {
        if (! in_array($media->mime_type, $this->allowedMimeTypesForConversion())) {
            return;
        }

        $webp_conversion = config('medialibrary.webp_conversion');

        if ($webp_conversion && $media->mime_type !== 'image/webp') {
            $mediaConversions = $this->model->mediaConversions;

            if ($this->without_conversions) {
                $mediaConversions = [];
            }

            WebpConversion::dispatch($media->id, $mediaConversions);

            return;
        }

        ThumbnailConversion::dispatch($media->id);

        if ($this->without_conversions) {
            return;
        }

        if (empty($this->model->mediaConversions)) {
            return;
        }

        MediaConversion::dispatch($media->id, $this->model->mediaConversions);
    }

    protected function allowedMimeTypesForConversion()
    {
        return ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    }
}
