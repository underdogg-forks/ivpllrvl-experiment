<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\UserClientsController;

Route::middleware('web')->group(function () {
    Route::get('user-clients', [UserClientsController::class, 'index'])->name('user-clients.index');
    Route::get('user-clients/user', [UserClientsController::class, 'user'])->name('user-clients.user');
    Route::post('user-clients/create', [UserClientsController::class, 'create'])->name('user-clients.create');
    Route::get('user-clients/delete', [UserClientsController::class, 'delete'])->name('user-clients.delete');
});
