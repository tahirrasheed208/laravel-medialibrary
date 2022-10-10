<?php

namespace TahirRasheed\MediaLibrary\View\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class Dropzone extends Component
{
    public string $name;
    public string $dropzone_id;
    public string $message;
    public int $max_file_size;
    public int $max_files = 20;
    public $model;

    public function __construct(string $name, string $message = null, Model $model = null)
    {
        $this->name = $name;
        $this->dropzone_id = "dropzone_{$name}";
        $this->model = $model;
        $this->message = $message ?: __('Drop files here or click to upload.');

        $this->max_file_size = round(config('medialibrary.max_file_size') / 1024 / 1024, 4);
    }

    public function render()
    {
        return view('medialibrary::components.dropzone');
    }
}
