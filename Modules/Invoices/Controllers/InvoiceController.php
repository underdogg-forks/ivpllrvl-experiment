<?php

namespace Modules\Invoices\Controllers;

use Modules\Invoices\Models\Invoice;

/**
 * Invoice Controller
 * 
 * Handles invoice-related HTTP requests
 * Migrated from CodeIgniter Invoices controller
 */
class InvoiceController
{
    /**
     * Display a listing of invoices.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $invoices = Invoice::with(['client', 'amounts'])
            ->orderBy('invoice_date_created', 'DESC')
            ->orderBy('invoice_number', 'DESC')
            ->paginate(15);

        return view('invoices::index', compact('invoices'));
    }

    /**
     * Display the specified invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show(int $id)
    {
        $invoice = Invoice::with(['client', 'items', 'amounts', 'taxRates'])
            ->findOrFail($id);

        return view('invoices::show', compact('invoice'));
    }

    /**
     * Show the form for creating a new invoice.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('invoices::create');
    }

    /**
     * Store a newly created invoice.
     *
     * @param  array  $data
     * @return Invoice
     */
    public function store(array $data): Invoice
    {
        $invoice = Invoice::query()->create($data);

        // Create invoice amount record
        $invoice->amounts()->create([
            'invoice_id' => $invoice->invoice_id,
        ]);

        return $invoice;
    }

    /**
     * Show the form for editing the specified invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(int $id)
    {
        $invoice = Invoice::query()->findOrFail($id);

        return view('invoices::edit', compact('invoice'));
    }

    /**
     * Update the specified invoice.
     *
     * @param  int  $id
     * @param  array  $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $invoice = Invoice::query()->findOrFail($id);

        return $invoice->update($data);
    }

    /**
     * Remove the specified invoice.
     *
     * @param  int  $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        $invoice = Invoice::query()->findOrFail($id);

        return $invoice->delete();
    }

    /**
     * Get invoices by status.
     *
     * @param  string  $status
     * @return \Illuminate\Contracts\View\View
     */
    public function byStatus(string $status)
    {
        $statusMap = [
            'draft' => 1,
            'sent' => 2,
            'viewed' => 3,
            'paid' => 4,
        ];

        $statusId = $statusMap[$status] ?? null;

        if (!$statusId) {
            abort(404);
        }

        $invoices = Invoice::byStatus($statusId)
            ->with(['client', 'amounts'])
            ->orderBy('invoice_date_created', 'DESC')
            ->paginate(15);

        return view('invoices::index', compact('invoices', 'status'));
    }

    /**
     * Get overdue invoices.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function overdue()
    {
        $invoices = Invoice::overdue()
            ->with(['client', 'amounts'])
            ->orderBy('invoice_date_due', 'ASC')
            ->paginate(15);

        return view('invoices::overdue', compact('invoices'));
    }
}
