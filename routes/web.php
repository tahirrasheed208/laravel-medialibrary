<?php

use Illuminate\Support\Facades\Route;
use TahirRasheed\MediaLibrary\Http\Controllers\MediaLibraryController;

Route::get('/medialibrary/uploader.min.js', [MediaLibraryController::class, 'uploader'])->name('medialibrary.uploader');
