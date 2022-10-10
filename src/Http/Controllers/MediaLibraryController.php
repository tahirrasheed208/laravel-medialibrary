<?php

namespace TahirRasheed\MediaLibrary\Http\Controllers;

use Illuminate\Http\Request;
use TahirRasheed\MediaLibrary\Services\DropzoneService;
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

        return (new DropzoneService)->upload($request->toArray());
    }

    public function delete(Request $request)
    {
        $request->validate([
            'file_name' => ['required', 'string'],
        ]);

        (new DropzoneService)->delete($request->toArray());

        return response()->json('success');
    }
}
