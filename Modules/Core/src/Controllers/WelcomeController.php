<?php

namespace Modules\Core\Controllers;

/**
 * WelcomeController.
 *
 * Handles the welcome/landing page display
 *
 * @legacy-file application/modules/welcome/controllers/Welcome.php
 */
class WelcomeController
{
    /**
     * Display the welcome/landing page.
     *
     * @return \Illuminate\View\View
     * @legacy-file application/modules/welcome/controllers/Welcome.php
     *
     * @legacy-function index
     *
     */
    public function index(): \Illuminate\View\View
    {
        return view('core::welcome');
    }
}
