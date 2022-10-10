<?php

namespace TahirRasheed\MediaLibrary\Services;

use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\MediaUpload;

class DropzoneService
{
    protected string $disk;
    protected array $request;

    public function __construct()
    {
        $this->disk = config('medialibrary.disk_name');
    }

    public function upload(array $request)
    {
        $this->request = $request;

        if (isset($request['model']) && !empty($request['model'])) {
            return $this->directAssignToModel();
        }

        return $this->temporaryUpload();
    }

    public function delete(array $request)
    {
        Storage::disk($this->disk)->delete('dropzone/temp/' . $request['file_name']);
    }

    protected function directAssignToModel()
    {
        $modelName = "\\App\Models\\" . $this->request['model'];
        $model = $modelName::findOrFail($this->request['model_id']);
        $response = [];

        foreach ($this->request['file'] as $file) {
            $media = (new MediaUpload)->setModel($model)
                ->upload($file, $this->request['type']);

            $response[] = [
                'media_id' => $media['media_id'],
                'file_name' => $file->getClientOriginalName(),
                'new_name' => $file->hashName(),
            ];
        }

        return $response;
    }

    protected function temporaryUpload()
    {
        $response = [];

        foreach ($this->request['file'] as $file) {
            $file->store('dropzone/temp', $this->disk);

            $response[] = [
                'file_name' => $file->getClientOriginalName(),
                'new_name' => $file->hashName(),
            ];
        }

        return $response;
    }
}
