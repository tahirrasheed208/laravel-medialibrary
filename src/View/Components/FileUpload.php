<?php

namespace TahirRasheed\MediaLibrary\View\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use TahirRasheed\MediaLibrary\Models\Media;

class FileUpload extends Component
{
    public string $name;

    public string $inputId;

    public bool $file = false;

    public bool $isImage = false;

    public string $thumbnail = '';

    public string $accept = '';

    public string $fileName = '';

    public function __construct(string $name, string $inputId = null, Model $model = null, mixed $setting = null, string $accept = '')
    {
        $this->name = $name;
        $this->inputId = !is_null($inputId) ?: $name;
        $this->accept = $this->setAcceptedFiles($accept);

        if ($setting) {
            $media = Media::find($setting);

            if ($media) {
                $this->file = true;
                $this->thumbnail = $media->getUrl('thumbnail');
                $this->isImage = $media->isImage();
                $this->fileName = $media->name;
            }
        }

        if ($model) {
            $media = $model->getMedia($name);

            if ($media) {
                $this->isImage = $media->isImage();
                $this->fileName = $media->name;
            }

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
            'lm-img-preview',
            'mb-2',
            'd-none' => !$this->file || !$this->isImage
        ]);
    }

    public function filePreviewClasses(): string
    {
        return Arr::toCssClasses([
            'lm-file-preview',
            'mb-2',
            'd-none' => !$this->file || $this->isImage
        ]);
    }

    public function uploadButtonClasses(): string
    {
        return Arr::toCssClasses([
            'btn',
            'btn-primary',
            'btn-sm',
            'lm-upload-button',
            'd-none' => $this->file
        ]);
    }

    public function removeButtonClasses(): string
    {
        return Arr::toCssClasses([
            'btn',
            'btn-danger',
            'btn-sm',
            'lm-remove-button',
            'd-none' => !$this->file
        ]);
    }

    protected function setAcceptedFiles($accept)
    {
        if (! empty($accept)) {
            return $accept;
        }

        return config('medialibrary.accept_files');
    }
}
