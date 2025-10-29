<?php

namespace Modules\Core\Http\Controllers;

use Modules\Invoices\Entities\Invoice;
use Modules\Invoices\Entities\InvoiceAmount;
use Modules\Quotes\Entities\Quote;
use Modules\Quotes\Entities\QuoteAmount;
use Modules\Crm\Entities\Project;
use Modules\Crm\Entities\Task;

/**
 * DashboardController
 * 
 * Displays the main dashboard with overview of invoices, quotes, projects, and tasks
 * Migrated from CodeIgniter Dashboard controller
 */
class DashboardController
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        // Get overview periods from settings
        $quoteOverviewPeriod = get_setting('quote_overview_period');
        $invoiceOverviewPeriod = get_setting('invoice_overview_period');
        
        // Get status totals
        $invoiceStatusTotals = InvoiceAmount::getStatusTotals($invoiceOverviewPeriod);
        $quoteStatusTotals = QuoteAmount::getStatusTotals($quoteOverviewPeriod);
        
        // Get recent invoices and quotes
        $invoices = Invoice::with('client')
            ->orderBy('invoice_date_created', 'desc')
            ->limit(10)
            ->get();
        
        $quotes = Quote::with('client')
            ->orderBy('quote_date_created', 'desc')
            ->limit(10)
            ->get();
        
        // Get overdue invoices
        $overdueInvoices = Invoice::with('client')
            ->whereRaw('invoice_date_due < CURDATE()')
            ->where('invoice_status_id', '<>', 4) // Not paid
            ->get();
        
        // Get recent projects and tasks
        $projects = Project::orderBy('project_date_created', 'desc')
            ->limit(10)
            ->get();
        
        $tasks = Task::orderBy('task_date_created', 'desc')
            ->limit(10)
            ->get();
        
        // Get statuses
        $invoiceStatuses = Invoice::statuses();
        $quoteStatuses = Quote::statuses();
        $taskStatuses = Task::statuses();
        
        // Prepare view data
        $data = [
            'invoice_status_totals' => $invoiceStatusTotals,
            'quote_status_totals' => $quoteStatusTotals,
            'invoice_status_period' => str_replace('-', '_', $invoiceOverviewPeriod),
            'quote_status_period' => str_replace('-', '_', $quoteOverviewPeriod),
            'invoices' => $invoices,
            'quotes' => $quotes,
            'invoice_statuses' => $invoiceStatuses,
            'quote_statuses' => $quoteStatuses,
            'overdue_invoices' => $overdueInvoices,
            'projects' => $projects,
            'tasks' => $tasks,
            'task_statuses' => $taskStatuses,
        ];
        
        return view('core::dashboard.index', $data);
    }
}

