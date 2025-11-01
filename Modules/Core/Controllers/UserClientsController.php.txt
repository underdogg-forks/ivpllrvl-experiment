<?php

namespace Modules\Core\Controllers;

use Illuminate\Support\Facades\DB;

use AllowDynamicProperties;
use Modules\Core\Services\UserClientsService;
use Modules\Core\Services\UsersService;
use Modules\Crm\app\Services\ClientsService;

#[AllowDynamicProperties]
class UserClientsController extends AdminController
{
    /**
     * Initialize the controller and perform the parent controller setup.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @originalName index
     *
     * @originalFile UserClientsController.php
     */
    public function index()
    {
        redirect()->route('users');
    }

    /**
     * @originalName user
     *
     * @originalFile UserClientsController.php
     */
    public function user($id = null)
    {
        if (request()->input('btn_cancel')) {
            redirect()->route('users');
        }
        $user = (new UsersService())->getById($id);
        if (empty($user)) {
            redirect()->route('users');
        }
        $user_clients = (new UserClientsService())->assignedTo($id)->get()->result();

        return view('user_clients.new', ['user' => $user, 'user_clients' => $user_clients]);
        $this->layout->set('id', $id);
        $this->layout->buffer('content', 'user_clients/field');
        $this->layout->render();
    }

    /**
     * @originalName create
     *
     * @originalFile UserClientsController.php
     */
    public function create($user_id = null)
    {
        if ( ! $user_id) {
            redirect()->route('custom_values');
        } elseif (request()->input('btn_cancel')) {
            redirect('user_clients/field/' . $user_id);
        }
        if ((new UserClientsService())->runValidation()) {
            if (request()->input('user_all_clients')) {
                $users_id = [$user_id];
                (new UserClientsService())->setAllClientsUser($users_id);
                $user_update = ['user_all_clients' => 1];
            } else {
                $user_update = ['user_all_clients' => 0];
                (new UserClientsService())->save();
            }
            DB::where('user_id', $user_id);
            DB::update('ip_users', $user_update);
            redirect('user_clients/user/' . $user_id);
        }
        $user    = (new UsersService())->getById($user_id);
        $clients = (new ClientsService())->getNotAssignedToUser($user_id);
        $this->layout->set(['id' => $user_id, 'user' => $user, 'clients' => $clients]);
    }

    /**
     * Delete a user-client relation and redirect to that user's client list.
     *
     * Deletes the user-client mapping identified by the given relation ID and redirects to
     * the 'user_clients/user/{user_id}' route for the associated user.
     *
     * @param int $user_client_id the ID of the user-client relation to remove
     */
    public function delete($user_client_id)
    {
        $ref = (new UserClientsService())->getById($user_client_id);
        (new UserClientsService())->delete($user_client_id);
        redirect('user_clients/user/' . $ref->user_id);
    }
}
