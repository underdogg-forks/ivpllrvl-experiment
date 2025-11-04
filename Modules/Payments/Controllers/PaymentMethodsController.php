<?php

namespace Modules\Payments\Controllers;

use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Services\PaymentMethodService;

use Modules\Core\Support\TranslationHelper;
/**
 * PaymentMethodsController
 *
 * Manages payment methods (Cash, Check, Credit Card, PayPal, etc.)
 *
 * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
 */
class PaymentMethodsController
{    public function __construct(
        protected PaymentMethodService $paymentMethodService
    ) {
    }

    /**
     * Display a paginated list of payment methods.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $paymentMethods = PaymentMethod::query()
            ->orderBy('payment_method_name')
            ->paginate(15, ['*'], 'page', $page);

        return view('payments::payment_methods_index', [
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Display form for creating or editing a payment method.
     *
     * @param int|null $id Payment method ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('payment_methods.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'payment_method_name' => 'required|string|max:255|unique:ip_payment_methods,payment_method_name' . ($id ? ',' . $id . ',payment_method_id' : ''),
            ]);

            if ($id) {
                $this->paymentMethodService->update($id, $validated);
            } else {
                $this->paymentMethodService->create($validated);
            }

            return redirect()->route('payment_methods.index')
                ->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }

        $paymentMethod = $id ? $this->paymentMethodService->find($id) : new PaymentMethod();
        if ($id && !$paymentMethod) {
            abort(404);
        }

        return view('payments::payment_methods_form', [
            'payment_method' => $paymentMethod,
            'is_update'      => (bool)$id,
        ]);
    }

    /**
     * Delete a payment method.
     *
     * @param int $id Payment method ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->paymentMethodService->delete($id);

        return redirect()->route('payment_methods.index')
            ->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}
