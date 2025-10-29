<?php

namespace Modules\Crm\Http\Controllers;

class GetController
{
    /** @legacy-file application/modules/guest/controllers/Get.php */
    public function index()
    {
        // Guest get/download operations
        return view('crm::guest_get');
    }
}
