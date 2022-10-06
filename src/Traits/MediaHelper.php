<?php

namespace TahirRasheed\MediaLibrary\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use TahirRasheed\MediaLibrary\Exceptions\FileSizeTooBigException;
use TahirRasheed\MediaLibrary\Models\Media;

trait MediaHelper
{
    public string $disk;
    public string $collection_name = '';
    public bool $without_conversions;
    public $model;

    public function disk(string $disk = null)
    {
        if (! $disk) {
            return $this;
        }

        $this->disk = $disk;

        return $this;
    }

    public function collection(string $collection_name = null)
    {
        if (! $collection_name) {
            return $this;
        }

        $this->collection_name = $collection_name;

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
}
