<?php

namespace TahirRasheed\MediaLibrary\Http\Controllers;

use TahirRasheed\MediaLibrary\Traits\CanPretendToBeAFile;

class MediaLibraryController extends Controller
{
    use CanPretendToBeAFile;

    public function uploader()
    {
        return $this->pretendResponseIsFile(__DIR__ . '/../../../resources/assets/js/uploader.js');
    }
}
