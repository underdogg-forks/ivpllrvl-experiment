<?php

namespace Modules\Core\Http\Controllers;

class WelcomeController
{
    /** @legacy-file application/modules/welcome/controllers/Welcome.php */
    public function index(): \Illuminate\View\View
    {
        // Welcome/landing page
        return view('core::welcome');
    }
}
