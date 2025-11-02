<?php

namespace Modules\Invoices\Controllers;

use Modules\Invoices\Models\InvoicesRecurring;
use Modules\Invoices\Services\InvoicesRecurringService;

class RecurringController
{
    /**
     * InvoicesRecurring service instance.
     *
     * @var InvoicesRecurringService
     */
    protected InvoicesRecurringService $invoicesRecurringService;

    /**
     * Constructor.
     *
     * @param InvoicesRecurringService $invoicesRecurringService
     */
    public function __construct(InvoicesRecurringService $invoicesRecurringService)
    {
        $this->invoicesRecurringService = $invoicesRecurringService;
    }
    /**
     * Display list of recurring invoices with filter.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/invoices/controllers/Recurring.php
     *
     * @legacy-line 32
     */
    public function index(int $page = 0)
    {
        $recurringInvoices = InvoicesRecurring::with(['invoice', 'client'])
            ->paginate(15, ['*'], 'page', $page);

        $recurFrequencies = InvoicesRecurring::RECUR_FREQUENCIES;

        return view('invoices::recurring_index', [
            'recurring_invoices' => $recurringInvoices,
            'recur_frequencies'  => $recurFrequencies,
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_invoices_recuring'),
            'filter_method'      => 'filter_invoices_recuring',
        ]);
    }

    /**
     * Stop a recurring invoice.
     *
     * @param int $invoiceRecurringId Recurring invoice ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function stop
     *
     * @legacy-file application/modules/invoices/controllers/Recurring.php
     *
     * @legacy-line 51
     */
    public function stop(int $invoiceRecurringId)
    {
        $this->invoicesRecurringService->stopRecurring($invoiceRecurringId);

        return redirect()->route('invoices.recurring.index')
            ->with('alert_success', trans('recurring_invoice_stopped'));
    }

    /**
     * Delete a recurring invoice configuration.
     *
     * @param int $invoiceRecurringId Recurring invoice ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     *
     * @legacy-file application/modules/invoices/controllers/Recurring.php
     *
     * @legacy-line 60
     */
    public function delete(int $invoiceRecurringId)
    {
        $this->invoicesRecurringService->delete($invoiceRecurringId);

        return redirect()->route('invoices.recurring.index')
            ->with('alert_success', trans('recurring_invoice_deleted'));
    }
}
