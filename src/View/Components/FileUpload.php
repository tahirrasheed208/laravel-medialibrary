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
        if ($this->isTailwind()) {
            return view('medialibrary::components.file-upload-tailwind');
        }

        return view('medialibrary::components.file-upload');
    }

    public function previewClasses(): string
    {
        $hidden = !$this->file || !$this->isImage;

        if ($this->isTailwind()) {
            return Arr::toCssClasses([
                'lm-img-preview',
                'mb-2',
                'd-none' => $hidden,
            ]);
        }

        return Arr::toCssClasses([
            'lm-img-preview',
            'mb-2',
            'd-none' => $hidden,
        ]);
    }

    public function filePreviewClasses(): string
    {
        $hidden = !$this->file || $this->isImage;

        if ($this->isTailwind()) {
            return Arr::toCssClasses([
                'lm-file-preview',
                'mb-2',
                'd-none' => $hidden,
            ]);
        }

        return Arr::toCssClasses([
            'lm-file-preview',
            'mb-2',
            'd-none' => $hidden,
        ]);
    }

    public function uploadButtonClasses(): string
    {
        if ($this->isTailwind()) {
            return Arr::toCssClasses([
                'px-3',
                'py-1.5',
                'text-sm',
                'font-medium',
                'text-white',
                'bg-blue-600',
                'rounded',
                'hover:bg-blue-700',
                'lm-upload-button',
                'd-none' => $this->file,
            ]);
        }

        return Arr::toCssClasses([
            'btn',
            'btn-primary',
            'btn-sm',
            'lm-upload-button',
            'd-none' => $this->file,
        ]);
    }

    public function removeButtonClasses(): string
    {
        if ($this->isTailwind()) {
            return Arr::toCssClasses([
                'px-3',
                'py-1.5',
                'text-sm',
                'font-medium',
                'text-white',
                'bg-red-600',
                'rounded',
                'hover:bg-red-700',
                'lm-remove-button',
                'd-none' => !$this->file,
            ]);
        }

        return Arr::toCssClasses([
            'btn',
            'btn-danger',
            'btn-sm',
            'lm-remove-button',
            'd-none' => !$this->file,
        ]);
    }

    protected function isTailwind(): bool
    {
        return config('medialibrary.theme', 'bootstrap') === 'tailwind';
    }

    protected function setAcceptedFiles($accept)
    {
        if (! empty($accept)) {
            return $accept;
        }

        return config('medialibrary.accept_files');
    }
}
