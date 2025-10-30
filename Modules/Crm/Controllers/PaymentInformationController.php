<?php

namespace Modules\Crm\Controllers;

class PaymentInformationController
{
    /** @legacy-file application/modules/guest/controllers/Payment_information.php */
    public function index(): \Illuminate\View\View
    {
        // Guest payment information display
        return view('crm::guest_payment_info');
    }
}
