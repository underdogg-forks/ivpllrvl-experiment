<?php

namespace Modules\Crm\Http\Controllers;

class ViewController
{
    /** @legacy-file application/modules/guest/controllers/View.php */
    public function index()
    {
        // Guest view operations
        return view('crm::guest_view');
    }
}
