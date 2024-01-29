<?php

namespace TahirRasheed\MediaLibrary\View\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class Dropzone extends Component
{
    public string $name;

    public string $dropzone_id;

    public string $message;

    public string $collection;

    public int $max_file_size;

    public int $max_files;

    public array $attachments = [];

    public string $accept = '';

    public $model;

    public function __construct(
        string $name,
        string $message = null,
        Model $model = null,
        string $collection = '',
        int $maxfiles = 0,
        string $accept = '',
        int|null $filesize = null,
        bool $hideimages = false,
    ) {
        $this->name = $name;
        $this->dropzone_id = "dropzone_{$name}";
        $this->model = $model;
        $this->collection = $collection;
        $this->message = $message ?: __('Drop files here or click to upload.');
        $this->max_files = !empty($maxfiles) ? $maxfiles : config('medialibrary.max_files');
        $this->max_file_size = $this->setMaxFilesSize($filesize);
        $this->accept = $this->setAcceptedFiles($accept);

        if ($model && ! $hideimages) {
            $attachments = $model->getAttachments($name);

            foreach ($attachments as $attachment) {
                $this->attachments[] = [
                    'mockFile' => [
                        'name' => $attachment->file_name,
                        'size' => $attachment->size,
                        'upload' => [
                            'filename' => $attachment->file_name,
                            'media_id' => $attachment->id,
                        ]
                    ],
                    'url' => $attachment->getUrl('thumbnail'),
                ];
            }
        }
    }

    public function render()
    {
        return view('medialibrary::components.dropzone');
    }

    protected function setMaxFilesSize($size)
    {
        if (! empty($size)) {
            return $size;
        }

        return round(config('medialibrary.max_file_size') / 1024 / 1024, 4);
    }

    protected function setAcceptedFiles($accept)
    {
        if (! empty($accept)) {
            return $accept;
        }

        return config('medialibrary.accept_files');
    }
}
