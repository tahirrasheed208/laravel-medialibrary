<?php

use Illuminate\Support\Facades\Route;
use TahirRasheed\MediaLibrary\Http\Controllers\MediaLibraryController;

Route::get('/medialibrary/uploader.min.js', [MediaLibraryController::class, 'uploader'])->name('medialibrary.uploader');

Route::post('/dropzone/upload', [MediaLibraryController::class, 'upload'])->name('medialibrary.dropzone.upload');
Route::post('/dropzone/delete', [MediaLibraryController::class, 'delete'])->name('medialibrary.dropzone.delete');
