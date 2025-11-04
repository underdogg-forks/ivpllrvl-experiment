<?php

namespace Modules\Core\Controllers;

use Modules\Invoices\Services\InvoiceAmountService;
use Modules\Invoices\Services\InvoiceService;
use Modules\Quotes\Services\QuoteAmountService;
use Modules\Quotes\Services\QuoteService;
use Modules\Projects\Services\ProjectService;
use Modules\Projects\Services\TaskService;

/**
 * DashboardController
 *
 * Handles the admin dashboard display
 *
 * @legacy-file application/modules/dashboard/controllers/Dashboard.php
 */
class DashboardController
{    /**
     * Initialize the DashboardController with dependency injection.
     *
     * @param InvoiceAmountService $invoiceAmountService
     * @param QuoteAmountService $quoteAmountService
     * @param InvoiceService $invoiceService
     * @param QuoteService $quoteService
     * @param ProjectService $projectService
     * @param TaskService $taskService
     */
    public function __construct(
        protected InvoiceAmountService $invoiceAmountService,
        protected QuoteAmountService $quoteAmountService,
        protected InvoiceService $invoiceService,
        protected QuoteService $quoteService,
        protected ProjectService $projectService,
        protected TaskService $taskService
    ) {
    }
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/dashboard/controllers/Dashboard.php
     */
    public function index(): \Illuminate\View\View
    {
        $quote_overview_period = get_setting('quote_overview_period');
        $invoice_overview_period = get_setting('invoice_overview_period');

        return view('core::dashboard_index', [
            'invoice_status_totals' => $this->invoiceAmountService->getStatusTotals($invoice_overview_period),
            'quote_status_totals' => $this->quoteAmountService->getStatusTotals($quote_overview_period),
            'invoice_status_period' => str_replace('-', '_', $invoice_overview_period),
            'quote_status_period' => str_replace('-', '_', $quote_overview_period),
            'invoices' => $this->invoiceService->getLatest(10),
            'quotes' => $this->quoteService->getLatest(10),
            'invoice_statuses' => $this->invoiceService->getStatuses(),
            'quote_statuses' => $this->quoteService->getStatuses(),
            'overdue_invoices' => $this->invoiceService->getOverdueInvoices(),
            'projects' => $this->projectService->getLatest(),
            'tasks' => $this->taskService->getLatest(),
            'task_statuses' => $this->taskService->getStatuses(),
        ]);
    }
}
