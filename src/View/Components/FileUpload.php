<?php

namespace TahirRasheed\MediaLibrary\View\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\View\Component;

class FileUpload extends Component
{
    public string $name;
    public string $inputId;
    public bool $file;
    public string $thumbnail;

    public function __construct(string $name, string $inputId = null, bool $file = false, string $thumbnail = '', Model $model = null)
    {
        $this->name = $name;
        $this->inputId = !is_null($inputId) ?: $name;
        $this->file = $file;
        $this->thumbnail = $thumbnail;

        if ($model) {
            $this->file = $model->hasMedia($name);
            $this->thumbnail = $model->getThumbnailUrl($name);
        }
    }

    public function render()
    {
        return view('medialibrary::components.file-upload');
    }

    public function previewClasses(): string
    {
        return Arr::toCssClasses([
            'main-img-preview',
            'mb-2',
            'd-none' => !$this->file
        ]);
    }

    public function uploadButtonClasses(): string
    {
        return Arr::toCssClasses([
            'btn',
            'btn-primary',
            'btn-sm',
            'ps-trigger-file',
            'd-none' => $this->file
        ]);
    }

    public function removeButtonClasses(): string
    {
        return Arr::toCssClasses([
            'btn',
            'btn-danger',
            'btn-sm',
            'htr-remove-file',
            'remove_img',
            'd-none' => !$this->file
        ]);
    }
}
