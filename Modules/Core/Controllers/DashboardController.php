<?php

namespace Modules\Core\Controllers;

class DashboardController
{
    /** @legacy-file application/modules/dashboard/controllers/Dashboard.php */
    public function index(): \Illuminate\View\View
    {
        // Dashboard statistics and overview
        $data = [
            'total_clients' => \Modules\Crm\Entities\Client::count(),
            'total_invoices' => \Modules\Invoices\Entities\Invoice::count(),
            'total_quotes' => \Modules\Quotes\Entities\Quote::count(),
        ];
        
        return view('core::dashboard', $data);
    }
}
