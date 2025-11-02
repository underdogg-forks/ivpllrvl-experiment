<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\app\Http\Controllers\AjaxController;
use Modules\Crm\app\Http\Controllers\ClientsController;

Route::middleware('web')->group(function () {
    Route::get('clients/name-query', [AjaxController::class, 'nameQuery'])->name('clients.name-query');
    Route::get('clients/get-latest', [AjaxController::class, 'getLatest'])->name('clients.get-latest');
    Route::post('clients/save-preference-permissive-search-clients', [AjaxController::class, 'savePreferencePermissiveSearchClients'])->name('clients.save-preference-permissive-search-clients');
    Route::get('clients/delete-client-note', [AjaxController::class, 'deleteClientNote'])->name('clients.delete-client-note');
    Route::post('clients/save-client-note', [AjaxController::class, 'saveClientNote'])->name('clients.save-client-note');
    Route::get('clients/load-client-notes', [AjaxController::class, 'loadClientNotes'])->name('clients.load-client-notes');
    Route::get('clients', [ClientsController::class, 'index'])->name('clients.index');
    Route::get('clients/status', [ClientsController::class, 'status'])->name('clients.status');
    Route::get('clients/form', [ClientsController::class, 'form'])->name('clients.form');
    Route::get('clients/view', [ClientsController::class, 'view'])->name('clients.view');
    Route::get('clients/delete', [ClientsController::class, 'delete'])->name('clients.delete');
});
