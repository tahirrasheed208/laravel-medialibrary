<?php

namespace TahirRasheed\MediaLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use TahirRasheed\MediaLibrary\Traits\CanPretendToBeAFile;

class MediaLibraryController extends Controller
{
    use CanPretendToBeAFile;

    public function uploader()
    {
        return $this->pretendResponseIsFile(__DIR__ . '/../../../dist/js/uploader.js');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'array', 'min:1'],
        ]);

        $disk = config('medialibrary.disk_name');

        $response = [];

        foreach ($request->file as $file) {
            $file->store('dropzone/temp', $disk);

            $response[] = [
                'file_name' => $file->getClientOriginalName(),
                'new_name' => $file->hashName(),
            ];
        }

        return $response;
    }

    public function delete(Request $request)
    {
        $request->validate([
            'file_name' => ['required', 'string'],
        ]);

        $disk = config('medialibrary.disk_name');

        Storage::disk($disk)->delete('dropzone/temp/' . $request->file_name);

        return response()->json('success');
    }
}
