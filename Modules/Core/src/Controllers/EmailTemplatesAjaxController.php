<?php

namespace App\Modules\Core\src\Controllers;

use App\Modules\EmailTemplates\Controllers\AllowDynamicProperties;
use Modules\Core\Controllers\AdminController;

#[AllowDynamicProperties]
class EmailTemplatesAjaxController extends AdminController
{
    public $ajax_controller = true;

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/email_templates/controllers/Ajax.php
     *
     * @legacy-function get_content()
     */
    public function get_content()
    {
        $this->load->model('email_templates/emailtemplate');

        $id = $this->input->post('email_template_id');

        echo json_encode($this->emailtemplates->get_by_id($id));
    }
}
