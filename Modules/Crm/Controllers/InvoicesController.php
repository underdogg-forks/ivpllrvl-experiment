<?php

namespace Modules\Crm\Controllers;

/**
 * InvoicesController (Guest).
 *
 * Guest portal invoice viewing
 *
 * @legacy-file application/modules/guest/controllers/Invoices.php
 */
class InvoicesController
{
    public function index()
    {
        // Guest invoice list
        return view('crm::guest_invoices');
    }

    public function view(string $urlKey)
    {
        // Guest invoice view by URL key
        $invoice = \Modules\Invoices\Models\Invoice::query()->where('invoice_url_key', $urlKey)->firstOrFail();

        return view('crm::guest_invoice_view', ['invoice' => $invoice]);
    }
}
