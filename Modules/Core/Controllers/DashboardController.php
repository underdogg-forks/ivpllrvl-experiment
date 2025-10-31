<?php

namespace Modules\Core\Controllers;

class DashboardController
{
    /** @legacy-file application/modules/dashboard/controllers/Dashboard.php */
    public function index(): \Illuminate\View\View
    {
        // Dashboard statistics and overview
        $data = [
            'total_clients'  => \Modules\Crm\Models\Client::count(),
            'total_invoices' => \Modules\Invoices\Models\Invoice::count(),
            'total_quotes'   => \Modules\Quotes\Models\Quote::count(),
        ];

        return view('core::dashboard', $data);
    }
}
