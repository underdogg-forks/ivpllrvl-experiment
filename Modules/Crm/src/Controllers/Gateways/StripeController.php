<?php

namespace Modules\Crm\Controllers\Gateways;

class StripeController
{
    /** @legacy-file application/modules/guest/controllers/gateways/Stripe.php */
    public function notify()
    {
        // Stripe webhook handler
        // TODO: Full Stripe integration
        return response('OK', 200);
    }
}
