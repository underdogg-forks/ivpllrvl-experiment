<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\UsersAjaxController;
use Modules\Core\Controllers\UsersController;

Route::middleware('web')->group(function () {
    Route::get('users/name-query', [UsersAjaxController::class, 'nameQuery'])->name('users.name-query');
    Route::get('users/get-latest', [UsersAjaxController::class, 'getLatest'])->name('users.get-latest');
    Route::post('users/save-preference-permissive-search-users', [UsersAjaxController::class, 'savePreferencePermissiveSearchUsers'])->name('users.save-preference-permissive-search-users');
    Route::post('users/save-user-client', [UsersAjaxController::class, 'saveUserClient'])->name('users.save-user-client');
    Route::get('users/load-user-client-table', [UsersAjaxController::class, 'loadUserClientTable'])->name('users.load-user-client-table');
    Route::get('users/modal-add-user-client', [UsersAjaxController::class, 'modalAddUserClient'])->name('users.modal-add-user-client');
    Route::get('users', [UsersController::class, 'index'])->name('users.index');
    Route::get('users/form', [UsersController::class, 'form'])->name('users.form');
    Route::get('users/change-password', [UsersController::class, 'changePassword'])->name('users.change-password');
    Route::get('users/delete', [UsersController::class, 'delete'])->name('users.delete');
    Route::get('users/delete-user-client', [UsersController::class, 'deleteUserClient'])->name('users.delete-user-client');
});
