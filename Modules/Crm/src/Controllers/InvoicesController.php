<?php

namespace Modules\Crm\Controllers;

use Modules\Invoices\Services\InvoiceService;

/**
 * InvoicesController (Guest).
 *
 * Guest portal invoice viewing
 *
 * @legacy-file application/modules/guest/controllers/Invoices.php
 */
class InvoicesController
{
    /**
     * Invoice service instance.
     *
     * @var InvoiceService
     */
    /**
     * Constructor.
     *
     * @param InvoiceService $invoiceService
     */
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function index()
    {
        // Guest invoice list
        return view('crm::guest_invoices');
    }

    public function view(string $urlKey)
    {
        // Guest invoice view by URL key
        $invoice = $this->invoiceService->getByUrlKey($urlKey);

        return view('crm::guest_invoice_view', ['invoice' => $invoice]);
    }
}
