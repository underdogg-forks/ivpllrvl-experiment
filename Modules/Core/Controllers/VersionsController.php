<?php

namespace Modules\Core\Controllers;

class VersionsController
{
    /** @legacy-file application/modules/settings/controllers/Versions.php */
    public function index(): \Illuminate\View\View
    {
        // Version information and updates
        return view('core::versions_index');
    }
}
