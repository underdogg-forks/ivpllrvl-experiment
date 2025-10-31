<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\CustomFieldsController;
use Modules\Core\Controllers\CustomValuesController;
use Modules\Core\Controllers\DashboardController;
use Modules\Core\Controllers\EmailTemplatesController;
use Modules\Core\Controllers\ImportController;
use Modules\Core\Controllers\LayoutController;
use Modules\Core\Controllers\MailerController;
use Modules\Core\Controllers\ReportsController;
use Modules\Core\Controllers\SessionsController;
use Modules\Core\Controllers\SettingsController;
use Modules\Core\Controllers\UploadController;
use Modules\Core\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| Core Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Settings
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'save'])->name('settings.save');

// Index routes
Route::get('/custom_fields', [CustomFieldsController::class, 'index'])->name('custom_fields.index');
Route::get('/custom_fields/index', [CustomFieldsController::class, 'index']);
Route::get('/custom_values', [CustomValuesController::class, 'index'])->name('custom_values.index');
Route::get('/email_templates/index', [EmailTemplatesController::class, 'index'])->name('email_templates.index');
Route::get('/import', [ImportController::class, 'index'])->name('import.index');

// Reports
Route::get('/reports/invoice_aging', [ReportsController::class, 'invoiceAging'])->name('reports.invoice_aging');
Route::get('/reports/invoices_per_client', [ReportsController::class, 'invoicesPerClient'])->name('reports.invoices_per_client');
Route::get('/reports/payment_history', [ReportsController::class, 'paymentHistory'])->name('reports.payment_history');
Route::get('/reports/sales_by_client', [ReportsController::class, 'salesByClient'])->name('reports.sales_by_client');
Route::get('/reports/sales_by_year', [ReportsController::class, 'salesByYear'])->name('reports.sales_by_year');

// Users (now part of Core module)
Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::get('/users/index', [UsersController::class, 'index']);
Route::get('/sessions/index', [SessionsController::class, 'index'])->name('sessions.index');

// Form routes (GET to display form)
Route::get('/custom_fields/form', [CustomFieldsController::class, 'form'])->name('custom_fields.form');
Route::get('/custom_fields/form/{id}', [CustomFieldsController::class, 'form']);
Route::get('/custom_values/create', [CustomValuesController::class, 'form'])->name('custom_values.create');
Route::get('/custom_values/create/{id}', [CustomValuesController::class, 'form']);
Route::get('/email_templates/form', [EmailTemplatesController::class, 'form'])->name('email_templates.form');
Route::get('/email_templates/form/{id}', [EmailTemplatesController::class, 'form']);
Route::get('/import/form', [ImportController::class, 'form'])->name('import.form');
Route::get('/users/form', [UsersController::class, 'form'])->name('users.form');
Route::get('/users/form/{id}', [UsersController::class, 'form']);
Route::get('/users/change_password/{id}', [UsersController::class, 'changePassword'])->name('users.change_password');

// POST routes for create/update
Route::post('/custom_fields/form', [CustomFieldsController::class, 'form']);
Route::post('/custom_fields/form/{id}', [CustomFieldsController::class, 'form']);
Route::post('/custom_values/create', [CustomValuesController::class, 'form']);
Route::post('/custom_values/create/{id}', [CustomValuesController::class, 'form']);
Route::post('/email_templates/form', [EmailTemplatesController::class, 'form']);
Route::post('/email_templates/form/{id}', [EmailTemplatesController::class, 'form']);
Route::post('/import/form', [ImportController::class, 'form']);
Route::post('/users/form', [UsersController::class, 'form']);
Route::post('/users/form/{id}', [UsersController::class, 'form']);
Route::post('/users/change_password/{id}', [UsersController::class, 'changePassword']);

// Delete routes (POST for safety)
Route::post('/custom_fields/delete/{id}', [CustomFieldsController::class, 'delete'])->name('custom_fields.delete');
Route::post('/custom_values/delete/{id}', [CustomValuesController::class, 'delete'])->name('custom_values.delete');
Route::post('/email_templates/delete/{id}', [EmailTemplatesController::class, 'delete'])->name('email_templates.delete');
Route::post('/users/delete/{id}', [UsersController::class, 'delete'])->name('users.delete');

// Sessions (authentication)
Route::get('/sessions/login', [SessionsController::class, 'login'])->name('sessions.login');
Route::post('/sessions/login', [SessionsController::class, 'login']);
Route::post('/sessions/logout', [SessionsController::class, 'logout'])->name('sessions.logout');

// Upload
Route::get('/upload/form', [UploadController::class, 'form'])->name('upload.form');
Route::post('/upload/delete/{id}', [UploadController::class, 'delete'])->name('upload.delete');

// AJAX routes
Route::get('/custom_fields/table/all', [CustomFieldsController::class, 'table'])->defaults('table', 'all');
Route::get('/custom_fields/table/client', [CustomFieldsController::class, 'table'])->defaults('table', 'client');
Route::get('/custom_fields/table/invoice', [CustomFieldsController::class, 'table'])->defaults('table', 'invoice');
Route::get('/custom_fields/table/payment', [CustomFieldsController::class, 'table'])->defaults('table', 'payment');
Route::get('/custom_fields/table/quote', [CustomFieldsController::class, 'table'])->defaults('table', 'quote');
Route::get('/custom_fields/table/user', [CustomFieldsController::class, 'table'])->defaults('table', 'user');
Route::get('/custom_values/field', [CustomValuesController::class, 'field'])->name('custom_values.field');
Route::get('/custom_values/field/{id}', [CustomValuesController::class, 'field']);
Route::get('/mailer/invoice/{id}', [MailerController::class, 'invoice'])->name('mailer.invoice');
Route::get('/mailer/quote/{id}', [MailerController::class, 'quote'])->name('mailer.quote');
Route::get('/layout/header', [LayoutController::class, 'header'])->name('layout.header');
Route::get('/layout/footer', [LayoutController::class, 'footer'])->name('layout.footer');
Route::get('/layout/sidebar', [LayoutController::class, 'sidebar'])->name('layout.sidebar');
Route::post('/upload/save', [UploadController::class, 'save'])->name('upload.save');
