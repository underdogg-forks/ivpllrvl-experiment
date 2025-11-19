<?php

use Illuminate\Support\Facades\Route;

// Core module web routes
Route::get('/core/health', function () {
    return response()->json(['module' => 'core', 'status' => 'ok']);
});
