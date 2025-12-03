<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\Controllers\ClientsAjaxController;
use Modules\Crm\Controllers\ClientsController;

Route::middleware('web')->group(function () {
    Route::get('clients/name-query', [ClientsAjaxController::class, 'nameQuery'])->name('clients.name-query');
    Route::get('clients/get-latest', [ClientsAjaxController::class, 'getLatest'])->name('clients.get-latest');
    Route::post('clients/save-preference-permissive-search-clients', [ClientsAjaxController::class, 'savePreferencePermissiveSearchClients'])->name('clients.save-preference-permissive-search-clients');
    Route::post('clients/delete-client-note', [ClientsAjaxController::class, 'deleteClientNote'])->name('clients.delete-client-note');
    Route::post('clients/save-client-note', [ClientsAjaxController::class, 'saveClientNote'])->name('clients.save-client-note');
    Route::get('clients/load-client-notes', [ClientsAjaxController::class, 'loadClientNotes'])->name('clients.load-client-notes');
    Route::get('clients', [ClientsController::class, 'index'])->name('clients.index');
    Route::get('clients/status', [ClientsController::class, 'status'])->name('clients.status');
    Route::get('clients/form/{client_id?}', [ClientsController::class, 'form'])->name('clients.form');
    Route::get('clients/view', [ClientsController::class, 'view'])->name('clients.view');
    Route::post('clients/delete', [ClientsController::class, 'delete'])->name('clients.delete');
});
