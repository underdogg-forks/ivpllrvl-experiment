<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\SessionsController;

Route::get('sessions', [SessionsController::class, 'index'])->name('sessions.index');

Route::get('sessions/login', [SessionsController::class, 'login'])
    ->middleware('guest')
    ->name('sessions.login');

Route::post('sessions/authenticate', [SessionsController::class, 'authenticate'])
    ->middleware('guest')
    ->name('sessions.authenticate');

Route::post('sessions/logout', [SessionsController::class, 'logout'])
    ->middleware('auth')
    ->name('sessions.logout');

Route::get('sessions/passwordreset', [SessionsController::class, 'passwordreset'])
    ->middleware('guest')
    ->name('sessions.passwordreset');
