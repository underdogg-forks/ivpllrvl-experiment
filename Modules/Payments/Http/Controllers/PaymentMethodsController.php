<?php

namespace Modules\Payments\Http\Controllers;

use Modules\Payments\Entities\PaymentMethod;

/**
 * PaymentMethodsController
 * 
 * Handles payment method management
 * Migrated from CodeIgniter Payment_Methods controller
 */
class PaymentMethodsController
{
    /**
     * Display a listing of payment methods.
     *
     * @param int $page
     * @return \Illuminate\Contracts\View\View
     */
    public function index($page = 0)
    {
        $payment_methods = PaymentMethod::ordered()
            ->paginate(15);

        return view('payments::payment_methods.index', [
            'payment_methods' => $payment_methods,
        ]);
    }

    /**
     * Show the form for creating/editing a payment method.
     *
     * @param int|null $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function form($id = null)
    {
        // Handle cancel button
        if (request()->has('btn_cancel')) {
            return redirect()->to('payment_methods');
        }

        // Handle form submission
        if (request()->has('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'payment_method_name' => 'required|string|max:255',
            ]);

            // Check for duplicates on create
            if (request()->input('is_update') == 0) {
                $existing = PaymentMethod::where('payment_method_name', $validated['payment_method_name'])->first();
                if ($existing) {
                    session()->flash('alert_error', trans('payment_method_already_exists'));
                    return redirect()->to('payment_methods/form');
                }
            }

            // Create or update payment method
            if ($id) {
                $payment_method = PaymentMethod::findOrFail($id);
                $payment_method->update($validated);
            } else {
                PaymentMethod::create($validated);
            }

            return redirect()->to('payment_methods');
        }

        // Load payment method for editing
        $payment_method = null;
        $is_update = false;
        if ($id) {
            $payment_method = PaymentMethod::find($id);
            if (!$payment_method) {
                abort(404);
            }
            $is_update = true;
        }

        return view('payments::payment_methods.form', [
            'payment_method' => $payment_method,
            'is_update' => $is_update,
        ]);
    }

    /**
     * Delete a payment method.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $payment_method = PaymentMethod::findOrFail($id);
        $payment_method->delete();

        return redirect()->to('payment_methods');
    }
}

