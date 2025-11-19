<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\SessionsController;

Route::middleware('web')->group(function () {
    Route::get('sessions', [SessionsController::class, 'index'])->name('sessions.index');
    Route::get('sessions/login', [SessionsController::class, 'login'])->name('sessions.login');
    Route::get('sessions/authenticate', [SessionsController::class, 'authenticate'])->name('sessions.authenticate');
    Route::get('sessions/logout', [SessionsController::class, 'logout'])->name('sessions.logout');
    Route::get('sessions/passwordreset', [SessionsController::class, 'passwordreset'])->name('sessions.passwordreset');
});
