<?php

namespace Modules\Core\Controllers;

/**
 * LayoutController.
 *
 * Handles layout configuration display
 *
 * @legacy-file application/modules/layout/controllers/Layout.php
 */
class LayoutController
{
    /**
     * Display layout configuration page.
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/layout/controllers/Layout.php
     */
    public function index(): \Illuminate\View\View
    {
        return view('core::layout_index');
    }
}
