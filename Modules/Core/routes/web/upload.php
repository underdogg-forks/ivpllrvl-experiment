<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\UploadController;

// SECURITY FIX: Added 'auth' middleware to protect upload operations
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('upload/upload-file', [UploadController::class, 'uploadFile'])->name('upload.upload-file');
    Route::post('upload/delete-file', [UploadController::class, 'deleteFile'])->name('upload.delete-file');
    Route::get('upload/show-files', [UploadController::class, 'showFiles'])->name('upload.show-files');
    Route::get('upload/get-file/{filename}', [UploadController::class, 'getFile'])->name('upload.get-file');
    Route::post('upload/create-dir', [UploadController::class, 'createDir'])->name('upload.create-dir');
});
