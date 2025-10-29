<?php

namespace Modules\Core\Http\Controllers;

class SetupController
{
    /** @legacy-file application/modules/setup/controllers/Setup.php */
    public function index(): \Illuminate\View\View
    {
        // Initial setup wizard
        return view('core::setup_index');
    }
}
