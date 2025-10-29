<?php

namespace Modules\Core\Controllers;

class MailerController
{
    /** @legacy-file application/modules/mailer/controllers/Mailer.php */
    public function index(): \Illuminate\View\View
    {
        // Email configuration and testing
        return view('core::mailer_index');
    }
}
