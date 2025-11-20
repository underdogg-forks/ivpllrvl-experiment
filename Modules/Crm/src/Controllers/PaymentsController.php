<?php

namespace Modules\Crm\Controllers;

/**
 * PaymentsController (Guest).
 *
 * Guest portal payment submission
 *
 * @legacy-file application/modules/guest/controllers/Payments.php
 */
class PaymentsController
{
    public function index()
    {
        // Guest payment form
        return view('crm::guest_payments');
    }

    public function submit()
    {
        // Process guest payment submission
        // TODO: Payment gateway integration
        return redirect()->back()->with('alert_success', trans('payment_submitted'));
    }
}
