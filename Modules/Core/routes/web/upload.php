<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\UploadController;

Route::middleware('web')->group(function () {
    Route::post('upload/upload-file', [UploadController::class, 'uploadFile'])->name('upload.upload-file');
    Route::get('upload/create-dir', [UploadController::class, 'createDir'])->name('upload.create-dir');
    Route::get('upload/show-files', [UploadController::class, 'showFiles'])->name('upload.show-files');
    Route::get('upload/delete-file', [UploadController::class, 'deleteFile'])->name('upload.delete-file');
    Route::get('upload/get-file', [UploadController::class, 'getFile'])->name('upload.get-file');
});
