<?php

namespace Modules\Core\Controllers;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class GuestController extends UserController
{
    /** @var array */
    public $user_clients = [];

    /**
     * Modules\Core\Controllers\Guest_Controller constructor.
     */
    public function __construct()
    {
        parent::__construct('user_type', 2);
// TODO: Use dependency injection - $this->load->model('user_clients/mdl_user_clients');
        $user_clients = (new UserClientsService())->assignedTo(session('user_id'))->get()->result();
        if ( ! $user_clients) {
            show_error(trans('guest_account_denied'), 403);
            exit;
        }
        foreach ($user_clients as $user_client) {
            $this->user_clients[$user_client->client_id] = $user_client->client_id;
        }
    }
}
