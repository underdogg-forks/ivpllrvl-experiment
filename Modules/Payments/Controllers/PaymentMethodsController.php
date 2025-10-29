<?php

namespace Modules\Payments\Http\Controllers;

use Modules\Payments\Entities\PaymentMethod;

/**
 * PaymentMethodsController
 * 
 * Manages payment methods (Cash, Check, Credit Card, PayPal, etc.)
 */
class PaymentMethodsController
{
    /**
     * Display a paginated list of payment methods
     * 
     * @param int $page Page number for pagination
     * @return \Illuminate\View\View
     * 
     * @legacy-function index
     * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
     * @legacy-line 32
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $paymentMethods = PaymentMethod::orderBy('payment_method_name')
            ->paginate(15, ['*'], 'page', $page);

        return view('payments::payment_methods_index', [
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Display form for creating or editing a payment method
     * 
     * @param int|null $id Payment method ID (null for create)
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * 
     * @legacy-function form
     * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
     * @legacy-line 42
     */
    public function form(?int $id = null)
    {
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->route('payment_methods.index');
        }

        // Handle form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            // Validate input with unique constraint
            $validated = request()->validate([
                'payment_method_name' => 'required|string|max:255|unique:ip_payment_methods,payment_method_name' . ($id ? ',' . $id . ',payment_method_id' : ''),
            ]);

            if ($id) {
                // Update existing
                $paymentMethod = PaymentMethod::findOrFail($id);
                $paymentMethod->update($validated);
            } else {
                // Create new
                PaymentMethod::create($validated);
            }

            return redirect()->route('payment_methods.index')
                ->with('alert_success', trans('record_successfully_saved'));
        }

        // Load existing record for editing
        if ($id) {
            $paymentMethod = PaymentMethod::find($id);
            if (!$paymentMethod) {
                abort(404);
            }
            $isUpdate = true;
        } else {
            $paymentMethod = new PaymentMethod();
            $isUpdate = false;
        }

        return view('payments::payment_methods_form', [
            'payment_method' => $paymentMethod,
            'is_update' => $isUpdate,
        ]);
    }

    /**
     * Delete a payment method
     * 
     * @param int $id Payment method ID
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @legacy-function delete
     * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
     * @legacy-line 78
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->delete();

        return redirect()->route('payment_methods.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
