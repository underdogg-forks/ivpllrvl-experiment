<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\Controllers\MailerController;

Route::middleware('web')->group(function () {
    Route::get('mailer/invoice', [MailerController::class, 'invoice'])->name('mailer.invoice');
    Route::get('mailer/quote', [MailerController::class, 'quote'])->name('mailer.quote');
    Route::get('mailer/send-invoice', [MailerController::class, 'sendInvoice'])->name('mailer.send-invoice');
    Route::get('mailer/send-quote', [MailerController::class, 'sendQuote'])->name('mailer.send-quote');
});
