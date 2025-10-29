<?php

namespace Modules\Core\Http\Controllers;

class LayoutController
{
    /** @legacy-file application/modules/layout/controllers/Layout.php */
    public function index(): \Illuminate\View\View
    {
        // Layout configuration
        return view('core::layout_index');
    }
}
