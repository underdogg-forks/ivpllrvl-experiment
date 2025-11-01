<?php

namespace Modules\Core\Controllers;

class DashboardController
{
    /**
     * @legacy-file application/modules/dashboard/controllers/Dashboard.php
     * Prepares data required by the admin dashboard and renders the dashboard view.
     *
     * The view data includes:
     * - `invoice_status_totals`, `quote_status_totals` — aggregated amounts by status for the configured overview periods.
     * - `invoice_status_period`, `quote_status_period` — overview period identifiers with `-` replaced by `_`.
     * - `invoices`, `quotes` — latest 10 invoices and quotes.
     * - `invoice_statuses`, `quote_statuses` — available invoice and quote statuses.
     * - `overdue_invoices` — invoices marked as overdue.
     * - `projects`, `tasks`, `task_statuses` — latest projects, latest tasks, and task statuses.
     *
     * @return string the rendered dashboard view content
     */
    public function index()
    {
        $quote_overview_period   = get_setting('quote_overview_period');
        $invoice_overview_period = get_setting('invoice_overview_period');

        return view('dashboard.index', ['invoice_status_totals' => (new InvoiceAmountsService())->getStatusTotals($invoice_overview_period), 'quote_status_totals' => (new QuoteAmountsService())->getStatusTotals($quote_overview_period), 'invoice_status_period' => str_replace('-', '_', $invoice_overview_period), 'quote_status_period' => str_replace('-', '_', $quote_overview_period), 'invoices' => (new InvoicesService())->limit(10)->get()->result(), 'quotes' => (new QuotesService())->limit(10)->get()->result(), 'invoice_statuses' => (new InvoicesService())->statuses(), 'quote_statuses' => (new QuotesService())->statuses(), 'overdue_invoices' => (new InvoicesService())->isOverdue()->get()->result(), 'projects' => (new ProjectsService())->getLatest()->get()->result(), 'tasks' => (new TasksService())->getLatest()->get()->result(), 'task_statuses' => (new TasksService())->statuses()]);
    }
}
