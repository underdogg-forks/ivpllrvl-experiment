<?php

use Illuminate\Support\Facades\Route;

// Core module API routes
Route::get('/core/ping', function () {
    return response()->json(['pong' => true]);
});
