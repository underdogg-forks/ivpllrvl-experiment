<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\AjaxController;
use Modules\Core\Controllers\EmailTemplatesController;

Route::middleware('web')->group(function () {
    Route::get('email-templates', [EmailTemplatesController::class, 'index'])->name('email-templates.index');
    Route::match(['get', 'post'], 'email-templates/form', [EmailTemplatesController::class, 'form'])->name('email-templates.form');
    Route::post('email-templates/delete/{id}', [EmailTemplatesController::class, 'delete'])->name('email-templates.delete');
    Route::get('email-templates/get-content', [AjaxController::class, 'getContent'])->name('email-templates.get-content');
});
