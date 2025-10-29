<?php

namespace Modules\Core\Http\Controllers;

class UploadController
{
    /** @legacy-file application/modules/upload/controllers/Upload.php */
    public function index()
    {
        // File upload handling
        return view('core::upload_index');
    }
}
