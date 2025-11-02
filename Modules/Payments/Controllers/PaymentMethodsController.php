<?php

namespace Modules\Payments\Controllers;

use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Services\PaymentMethodService;

/**
 * PaymentMethodsController.
 *
 * Manages payment methods (Cash, Check, Credit Card, PayPal, etc.)
 */
class PaymentMethodsController
{
    /**
     * PaymentMethod service instance.
     *
     * @var PaymentMethodService
     */
    protected PaymentMethodService $paymentMethodService;

    /**
     * Constructor.
     *
     * @param PaymentMethodService $paymentMethodService
     */
    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }
    /**
     * Display a paginated list of payment methods.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
     *
     * @legacy-line 32
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $paymentMethods = $this->paymentMethodService->getAllPaginated(15, $page);

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
     *
     * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
     *
     * @legacy-line 42
     */
    public function form($id = null)
    {
        if (request()->input('btn_cancel')) {
            redirect()->route('payment_methods');
        }
        $this->filterInput();
        // <<<--- filters _POST array for nastiness
        if (request()->input('is_update') == 0 && request()->input('payment_method_name') != '') {
            $check = DB::get_where('ip_payment_methods', ['payment_method_name' => request()->input('payment_method_name')])->result();
            if ( ! empty($check)) {
                session()->flash('alert_error', trans('payment_method_already_exists'));
                redirect()->route('payment_methods/form');
            }
        }
        if ((new PaymentMethodsService())->runValidation()) {
            (new PaymentMethodsService())->save($id);
            redirect()->route('payment_methods');
        }
        if ($id && ! request()->input('btn_submit')) {
            if ( ! (new PaymentMethodsService())->prepForm($id)) {
                show_404();
            }
            (new PaymentMethodsService())->setFormValue('is_update', true);
        }

        return view('payments::payment_methods_form', [
            'payment_method' => $paymentMethod,
            'is_update'      => $isUpdate,
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
     *
     * @legacy-file application/modules/payment_methods/controllers/Payment_methods.php
     *
     * @legacy-line 78
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->paymentMethodService->delete($id);

        return redirect()->route('payment_methods.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
