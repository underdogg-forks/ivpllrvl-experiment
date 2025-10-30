<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Controllers\FamiliesController;
use Modules\Products\Controllers\ProductsController;
use Modules\Products\Controllers\UnitsController;

/*
|--------------------------------------------------------------------------
| Products Web Routes
|--------------------------------------------------------------------------
*/

// Index routes
Route::get('/families/index', [FamiliesController::class, 'index'])->name('families.index');
Route::get('/units/index', [UnitsController::class, 'index'])->name('units.index');
Route::get('/products/index', [ProductsController::class, 'index'])->name('products.index');

// Form routes (GET to display form)
Route::get('/families/form', [FamiliesController::class, 'form'])->name('families.form');
Route::get('/families/form/{id}', [FamiliesController::class, 'form']);
Route::get('/units/form', [UnitsController::class, 'form'])->name('units.form');
Route::get('/units/form/{id}', [UnitsController::class, 'form']);
Route::get('/products/form', [ProductsController::class, 'form'])->name('products.form');
Route::get('/products/form/{id}', [ProductsController::class, 'form']);

// POST routes for create/update
Route::post('/families/form', [FamiliesController::class, 'form']);
Route::post('/families/form/{id}', [FamiliesController::class, 'form']);
Route::post('/units/form', [UnitsController::class, 'form']);
Route::post('/units/form/{id}', [UnitsController::class, 'form']);
Route::post('/products/form', [ProductsController::class, 'form']);
Route::post('/products/form/{id}', [ProductsController::class, 'form']);

// Delete routes (POST for safety)
Route::post('/families/delete/{id}', [FamiliesController::class, 'delete'])->name('families.delete');
Route::post('/units/delete/{id}', [UnitsController::class, 'delete'])->name('units.delete');
Route::post('/products/delete/{id}', [ProductsController::class, 'delete'])->name('products.delete');
