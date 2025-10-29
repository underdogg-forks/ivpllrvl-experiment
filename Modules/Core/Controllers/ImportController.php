<?php

namespace Modules\Core\Controllers;

class ImportController
{
    /** @legacy-file application/modules/import/controllers/Import.php */
    public function index(): \Illuminate\View\View
    {
        // Data import functionality
        return view('core::import_index');
    }
}
