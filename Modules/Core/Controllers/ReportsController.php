<?php

namespace Modules\Core\Controllers;

class ReportsController
{
    /** @legacy-file application/modules/reports/controllers/Reports.php */
    public function index(): \Illuminate\View\View
    {
        // Financial reports and analytics
        return view('core::reports_index');
    }
}
