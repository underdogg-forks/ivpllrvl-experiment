<?php

namespace Modules\Crm\Controllers\Gateways;

class PaypalController
{
    /** @legacy-file application/modules/guest/controllers/gateways/Paypal.php */
    public function notify()
    {
        // PayPal IPN notification handler
        // TODO: Full PayPal integration
        return response('OK', 200);
    }
}
