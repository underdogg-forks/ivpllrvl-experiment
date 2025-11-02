<?php

namespace Modules\Payments\Controllers;

use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Services\PaymentService;
use Modules\Payments\Services\PaymentMethodService;

/**
 * AjaxController.
 *
 * Handles AJAX requests for payments
 * Migrated from CodeIgniter Ajax controller
 */
class AjaxController
{
    /**
     * Payment service instance.
     *
     * @var PaymentService
     */
    protected PaymentService $paymentService;

    /**
     * Payment method service instance.
     *
     * @var PaymentMethodService
     */
    protected PaymentMethodService $paymentMethodService;

    /**
     * Constructor.
     *
     * @param PaymentService       $paymentService
     * @param PaymentMethodService $paymentMethodService
     */
    public function __construct(
        PaymentService $paymentService,
        PaymentMethodService $paymentMethodService
    ) {
        $this->paymentService       = $paymentService;
        $this->paymentMethodService = $paymentMethodService;
    }

    /**
     * Add a payment via AJAX.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add()
    {
        // Validate input
        $validator = validator(request()->all(), [
            'invoice_id'        => 'required|integer',
            'payment_date'      => 'required|date',
            'payment_amount'    => 'required|numeric',
            'payment_method_id' => 'nullable|integer',
            'payment_note'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'           => 0,
                'validation_errors' => $validator->errors()->toArray(),
            ]);
        }

        $payment = $this->paymentService->create($validator->validated());

        return response()->json([
            'success'    => 1,
            'payment_id' => $payment->payment_id,
        ]);
    }

    /**
     * Display modal for adding payment.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function modal_add_payment()
    {
        $payment_methods = PaymentMethod::ordered()->get();

        $data = [
            'payment_methods'        => $payment_methods,
            'invoice_id'             => request()->post('invoice_id'),
            'invoice_balance'        => request()->post('invoice_balance'),
            'invoice_payment_method' => request()->post('invoice_payment_method'),
            'payment_cf_exist'       => request()->post('payment_cf_exist'),
        ];

        return view('payments::modal_add_payment', $data);
    }
}
