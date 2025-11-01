<?php

namespace Modules\Core\Controllers;

use Illuminate\Support\Facades\DB;

use AllowDynamicProperties;
use Modules\Users\Controllers\ClientsService;
use Modules\Users\Controllers\SettingsService;
use Modules\Users\Controllers\UserClientsService;
use Modules\Users\Controllers\UsersService;

#[AllowDynamicProperties]
class UsersAjaxController extends AdminController
{
    public $ajax_controller = true;

    /**
     * Outputs matching active users of a given type as JSON objects for select lists.
     *
     * Reads 'query' and 'permissive_search_users' from the request. If 'query' is empty,
     * the method outputs an empty JSON array and exits early. Otherwise it outputs a JSON
     * array of objects with `id` (user_id) and `text` (formatted user label) for active users
     * of the specified type whose name, company, or invoicing contact matches the query.
     * When 'permissive_search_users' is truthy, matching allows the query to appear in the middle of fields.
     *
     * @param int $type the user_type filter to apply (default: 1)
     */
    public function nameQuery($type = 1)
    {
        // Load the model & helper
// TODO: Laravel autoloads helpers - $this->load->helper('user');
        $response = [];
        // GetController the post input
        $query                 = request()->query('query');
        $permissiveSearchUsers = request()->query('permissive_search_users');
        if (empty($query)) {
            echo json_encode($response);
            exit;
        }
        // Search for chars "in the middle" of users names
        $moreUsersQuery = $permissiveSearchUsers ? '%' : '';
        // Search for users $type
        $escapedQuery = DB::escape_str($query);
        $escapedQuery = str_replace('%', '', $escapedQuery);
        // Not searched: user_address_1 user_address_2 user_city user_state user_zip user_country user_invoicing_contact
        $users = (new UsersService())->where('user_active', 1)->where('user_type', $type)->having("user_name LIKE '" . $moreUsersQuery . $escapedQuery . "%'")->or_having("user_company LIKE '" . $moreUsersQuery . $escapedQuery . "%'")->or_having("user_invoicing_contact LIKE '" . $moreUsersQuery . $escapedQuery . "%'")->orderBy('user_name')->get()->result();
        foreach ($users as $user) {
            $response[] = ['id' => $user->user_id, 'text' => format_user($user)];
        }
        // Return the results
        echo json_encode($response);
    }

    /**
     * Retrieve up to five active users ordered by creation date and format them for select lists.
     *
     * Echoes a JSON-encoded array of items where each item contains:
     * - `id`: the user's `user_id`
     * - `text`: the HTML-escaped formatted user label
     *
     * The results are limited to five users and ordered by `user_date_created`.
     */
    public function getLatest()
    {
        // Load the model & helper
        $response = [];
        $users    = (new UsersService())->where('user_active', 1)->limit(5)->orderBy('user_date_created')->get()->result();
        foreach ($users as $user) {
            $response[] = ['id' => $user->user_id, 'text' => htmlsc(format_user($user))];
        }
        // Return the results
        echo json_encode($response);
    }

    /**
     * Save the user's permissive-user-search preference.
     *
     * Validates that the 'permissive_search_users' request value is '0' or '1'; if valid, stores it under the 'enable_permissive_search_users' setting. If the value is invalid, no setting is changed.
     */
    public function savePreferencePermissiveSearchUsers()
    {
        $permissiveSearchUsers = request()->query('permissive_search_users');
        if ( ! preg_match('!^[0-1]{1}$!', $permissiveSearchUsers)) {
            exit;
        }
        (new SettingsService())->save('enable_permissive_search_users', $permissiveSearchUsers);
    }

    /**
     * Associate a client with an existing user or queue the client for a new user.
     *
     * If the provided client ID corresponds to an existing client and a user ID is supplied,
     * creates a user-client association if one does not already exist. If no user ID is supplied,
     * stores the client ID in the session under 'user_clients' for association after user creation.
     * If the client ID is not found, no action is taken.
     */
    public function saveUserClient()
    {
        $user_id   = request()->input('user_id');
        $client_id = request()->input('client_id');
        $client    = (new ClientsService())->getById($client_id);
        if ($client) {
            $client_id = $client->client_id;
            // Is this a new user or an existing user?
            if ( ! empty($user_id)) {
                // Existing user - go ahead and save the entries
                $user_client = (new UserClientsService())->where('ip_user_clients.user_id', $user_id)->where('ip_user_clients.client_id', $client_id)->get();
                if ( ! $user_client->numRows()) {
                    (new UserClientsService())->save(null, ['user_id' => $user_id, 'client_id' => $client_id]);
                }
            } else {
                // New user - assign the entries to a session variable until user record is saved
                $user_clients             = session('user_clients') ? session('user_clients') : [];
                $user_clients[$client_id] = $client_id;
                session(['user_clients', $user_clients);
            }
        }
    }

    /**
     * Render the partial user-client table populated from either session-stored client IDs or the posted user's client associations.
     *
     * When the session key `user_clients` exists, loads clients matching those IDs and passes them to the partial view with `id` set to null.
     * Otherwise, loads client associations for the posted `user_id` and passes them to the partial view with `id` set to that `user_id`.
     *
     * @originalName loadUserClientTable
     *
     * @originalFile AjaxController.php
     */
    public function loadUserClientTable()
    {
        $session_user_clients = session('user_clients');
        if ($session_user_clients) {
            $data = ['id' => null, 'user_clients' => (new ClientsService())->where_in('ip_clients.client_id', $session_user_clients)->get()->result()];
        } else {
            $data = ['id' => request()->input('user_id'), 'user_clients' => (new UserClientsService())->where('ip_user_clients.user_id', request()->input('user_id'))->get()->result()];
        }
        $this->layout->loadView('users/partial_user_client_table', $data);
    }

    /**
     * Render the modal for adding a client association to a user.
     *
     * Prepares a list of clients: if session 'user_clients' exists, returns clients not in that session list;
     * otherwise returns clients not already assigned to the specified user. Renders the 'users/modal_user_client' view
     * with keys 'user_id' and 'clients'.
     *
     * @param int|null $user_id the user ID whose assigned clients should be excluded from the selection; if null, session 'user_clients' determines excluded clients
     */
    public function modalAddUserClient($user_id = null)
    {
        if ($session_user_clients = session('user_clients')) {
            $clients          = (new ClientsService())->where_not_in('ip_clients.client_id', $session_user_clients)->get()->result();
            $assigned_clients = [];
        } else {
            $assigned_clients_query = (new UserClientsService())->where('ip_user_clients.user_id', $user_id)->get()->result();
            $assigned_clients       = [];
            foreach ($assigned_clients_query as $assigned_client) {
                $assigned_clients[] = (int) $assigned_client->client_id;
            }
            if ($assigned_clients === []) {
                $clients = (new ClientsService())->get()->result();
            } else {
                $clients = (new ClientsService())->where_not_in('ip_clients.client_id', $assigned_clients)->get()->result();
            }
        }
        $data = ['user_id' => $user_id, 'clients' => $clients];
        $this->layout->loadView('users/modal_user_client', $data);
    }
}
