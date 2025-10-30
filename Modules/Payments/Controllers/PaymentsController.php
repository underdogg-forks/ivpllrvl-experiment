<?php

namespace Modules\Payments\Controllers;

use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Models\PaymentLog;

/**
 * PaymentsController
 *
 * Handles payment recording and tracking
 */
class PaymentsController
{
    /**
     * Display a paginated list of payments
     *
     * @param int $page Page number for pagination
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/payments/controllers/Payments.php
     * @legacy-line 32
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $payments = Payment::with(['invoice', 'paymentMethod'])
            ->orderBy('payment_date', 'desc')
            ->paginate(15, ['*'], 'page', $page);

        return view('payments::index', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_payments'),
            'filter_method' => 'filter_payments',
            'payments' => $payments,
        ]);
    }

    /**
     * Display form for creating or editing a payment
     *
     * Note: Simplified custom fields handling - full implementation pending
     *
     * @param int|null $id Payment ID (null for create)
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     * @legacy-file application/modules/payments/controllers/Payments.php
     * @legacy-line 50
     */
    public function form(?int $id = null)
    {
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->route('payments.index');
        }

        // Handle form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'invoice_id' => 'required|integer|exists:ip_invoices,invoice_id',
                'payment_date' => 'required|date',
                'payment_amount' => 'required|numeric|min:0',
                'payment_method_id' => 'nullable|integer|exists:ip_payment_methods,payment_method_id',
                'payment_note' => 'nullable|string',
            ]);

            if ($id) {
                // Update existing
                $payment = Payment::query()->findOrFail($id);
                $payment->update($validated);
            } else {
                // Create new
                $payment = Payment::query()->create($validated);
                $id = $payment->payment_id;
            }

            // Handle custom fields if present
            // Note: Custom field handling deferred to Custom module implementation
            $customData = request()->input('custom', []);
            // TODO: Save custom fields when Custom module is fully integrated

            return redirect()->route('payments.index')
                ->with('alert_success', trans('record_successfully_saved'));
        }

        // Load payment for editing
        if ($id) {
            $payment = Payment::with(['invoice', 'paymentMethod'])->find($id);
            if (!$payment) {
                abort(404);
            }
        } else {
            $payment = new Payment();
        }

        // Load related data
        $paymentMethods = PaymentMethod::query()->orderBy('payment_method_name')->get();

        // Load open invoices (invoices with balance > 0)
        $openInvoices = \Modules\Invoices\Models\Invoice::query()->where('invoice_balance', '>', 0)
            ->with('client')
            ->orderBy('invoice_date_created', 'desc')
            ->get();

        // Custom fields - deferred to Custom module
        $customFields = [];
        $customValues = [];

        return view('payments::form', [
            'payment_id' => $id,
            'payment' => $payment,
            'payment_methods' => $paymentMethods,
            'open_invoices' => $openInvoices,
            'custom_fields' => $customFields,
            'custom_values' => $customValues,
        ]);
    }

    /**
     * Display online payment logs (PayPal, Stripe, etc.)
     *
     * @param int $page Page number for pagination
     * @return \Illuminate\View\View
     *
     * @legacy-function online_logs
     * @legacy-file application/modules/payments/controllers/Payments.php
     * @legacy-line 156
     */
    public function onlineLogs(int $page = 0): \Illuminate\View\View
    {
        $paymentLogs = PaymentLog::with('invoice')
            ->orderBy('payment_log_date', 'desc')
            ->paginate(15, ['*'], 'page', $page);

        return view('payments::online_logs', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_online_logs'),
            'filter_method' => 'filter_online_logs',
            'payment_logs' => $paymentLogs,
        ]);
    }

    /**
     * Delete a payment
     *
     * @param int $id Payment ID
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/payments/controllers/Payments.php
     * @legacy-line 179
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $payment = Payment::query()->findOrFail($id);
        $payment->delete();

        return redirect()->route('payments.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
