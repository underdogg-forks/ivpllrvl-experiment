<?php

namespace Modules\Core\Controllers;

/**
 * VersionsController.
 *
 * Displays version information and update notifications
 *
 * @legacy-file application/modules/settings/controllers/Versions.php
 */
class VersionsController
{
    /**
     * Display version information and update status.
     *
     * @return \Illuminate\View\View
     * @legacy-file application/modules/settings/controllers/Versions.php
     *
     * @legacy-function index
     *
     */
    public function index(): \Illuminate\View\View
    {
        return view('core::versions_index');
    }
}
