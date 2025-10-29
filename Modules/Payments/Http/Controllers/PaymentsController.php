<?php

namespace Modules\Payments\Http\Controllers;

use Modules\Payments\Entities\Payment;
use Modules\Payments\Entities\Payment_method;
use Modules\Payments\Entities\Payment_log;

/**
 * PaymentsController
 * 
 * Handles payment management
 * Migrated from CodeIgniter Payments controller
 */
class PaymentsController
{
    /**
     * Display a listing of payments.
     *
     * @param int $page
     * @return \Illuminate\Contracts\View\View
     */
    public function index($page = 0)
    {
        $payments = Payment::with(['invoice.client', 'invoice.invoiceAmount', 'paymentMethod'])
            ->ordered()
            ->paginate(15);

        return view('payments::payments.index', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_payments'),
            'filter_method' => 'filter_payments',
            'payments' => $payments,
        ]);
    }

    /**
     * Show the form for creating/editing a payment.
     *
     * @param int|null $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function form($id = null)
    {
        // Handle cancel button
        if (request()->has('btn_cancel')) {
            return redirect()->to('payments');
        }

        // Handle form submission
        if (request()->has('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'invoice_id' => 'required|integer',
                'payment_date' => 'required|date',
                'payment_amount' => 'required|numeric',
                'payment_method_id' => 'nullable|integer',
                'payment_note' => 'nullable|string',
            ]);

            // Handle custom fields if present
            $custom = request()->input('custom', []);

            // Create or update payment
            if ($id) {
                $payment = Payment::findOrFail($id);
                $payment->update($validated);
            } else {
                $payment = Payment::create($validated);
                $id = $payment->payment_id;
            }

            // Save custom fields (TODO: implement custom field handling)
            // $this->saveCustomFields($id, $custom);

            return redirect()->to('payments');
        }

        // Load payment for editing
        $payment = null;
        if ($id) {
            $payment = Payment::with(['invoice', 'paymentMethod'])->find($id);
            if (!$payment) {
                abort(404);
            }
        }

        // Load related data
        $payment_methods = Payment_method::ordered()->get();
        
        // TODO: Load open invoices
        // $open_invoices = Invoice::isOpen()->get();
        $open_invoices = [];

        // TODO: Load custom fields
        $custom_fields = [];
        $custom_values = [];

        return view('payments::payments.form', [
            'payment_id' => $id,
            'payment' => $payment,
            'payment_methods' => $payment_methods,
            'open_invoices' => $open_invoices,
            'custom_fields' => $custom_fields,
            'custom_values' => $custom_values,
        ]);
    }

    /**
     * Display online payment logs.
     *
     * @param int $page
     * @return \Illuminate\Contracts\View\View
     */
    public function online_logs($page = 0)
    {
        $payment_logs = Payment_log::with('invoice')
            ->ordered()
            ->paginate(15);

        return view('payments::payments.online_logs', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_online_logs'),
            'filter_method' => 'filter_online_logs',
            'payment_logs' => $payment_logs,
        ]);
    }

    /**
     * Delete a payment.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return redirect()->to('payments');
    }
}

