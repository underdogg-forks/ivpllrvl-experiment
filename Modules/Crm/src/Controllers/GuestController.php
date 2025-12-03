<?php

namespace Modules\Crm\Controllers;

class GuestController
{
    /** @legacy-file application/modules/guest/controllers/Guest.php */
    public function index(): \Illuminate\View\View
    {
        // Guest portal home page
        return view('crm::guest_index');
    }
}
