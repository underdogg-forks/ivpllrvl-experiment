<?php

namespace Modules\Core\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Core\Models\User;
use Modules\Core\Services\UserService;
use Modules\Core\Services\SettingsService;
use Modules\Crm\Services\ClientService;
use Modules\Crm\Services\UserClientService;

/**
 * UsersAjaxController
 *
 * Handles AJAX requests for user-related operations
 *
 * @legacy-file application/modules/users/controllers/Ajax.php
 */
class UsersAjaxController
{
    protected UserService $userService;
    protected ClientService $clientService;
    protected UserClientService $userClientService;
    protected SettingsService $settingsService;

    /**
     * Initialize the UsersAjaxController with dependency injection.
     *
     * @param UserService $userService
     * @param ClientService $clientService
     * @param UserClientService $userClientService
     * @param SettingsService $settingsService
     */
    public function __construct(
        UserService $userService,
        ClientService $clientService,
        UserClientService $userClientService,
        SettingsService $settingsService
    ) {
        $this->userService = $userService;
        $this->clientService = $clientService;
        $this->userClientService = $userClientService;
        $this->settingsService = $settingsService;
    }

    /**
     * Search for users by name query (AJAX endpoint).
     *
     * @param int $type User type filter (default: 1)
     *
     * @return void Outputs JSON response
     *
     * @legacy-function nameQuery
     * @legacy-file application/modules/users/controllers/Ajax.php
     */
    public function nameQuery(int $type = 1): void
    {
        $response = [];
        $query = request()->query('query');
        $permissiveSearchUsers = request()->query('permissive_search_users');
        
        if (empty($query)) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        // Search for chars "in the middle" of users names
        $moreUsersQuery = $permissiveSearchUsers ? '%' : '';
        $escapedQuery = str_replace('%', '', $query);

        // Search for users by type
        $users = User::query()
            ->where('user_active', 1)
            ->where('user_type', $type)
            ->where(function ($q) use ($escapedQuery, $moreUsersQuery) {
                $q->where('user_name', 'LIKE', $moreUsersQuery . $escapedQuery . '%')
                  ->orWhere('user_company', 'LIKE', $moreUsersQuery . $escapedQuery . '%')
                  ->orWhere('user_invoicing_contact', 'LIKE', $moreUsersQuery . $escapedQuery . '%');
            })
            ->orderBy('user_name')
            ->get();

        foreach ($users as $user) {
            $response[] = ['id' => $user->user_id, 'text' => format_user($user)];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Get latest active users (AJAX endpoint).
     *
     * @return void Outputs JSON response
     *
     * @legacy-function getLatest
     * @legacy-file application/modules/users/controllers/Ajax.php
     */
    public function getLatest(): void
    {
        $response = [];
        $users = User::query()
            ->where('user_active', 1)
            ->limit(5)
            ->orderBy('user_date_created')
            ->get();

        foreach ($users as $user) {
            $response[] = ['id' => $user->user_id, 'text' => htmlsc(format_user($user))];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Save user's permissive search preference (AJAX endpoint).
     *
     * @return void
     *
     * @legacy-function savePreferencePermissiveSearchUsers
     * @legacy-file application/modules/users/controllers/Ajax.php
     */
    public function savePreferencePermissiveSearchUsers(): void
    {
        $permissiveSearchUsers = request()->query('permissive_search_users');
        if (!preg_match('!^[0-1]{1}$!', $permissiveSearchUsers)) {
            exit;
        }
        $this->settingsService->save('enable_permissive_search_users', $permissiveSearchUsers);
    }

    /**
     * Save user-client association (AJAX endpoint).
     *
     * @return void
     *
     * @legacy-function saveUserClient
     * @legacy-file application/modules/users/controllers/Ajax.php
     */
    public function saveUserClient(): void
    {
        $user_id = request()->input('user_id');
        $client_id = request()->input('client_id');
        $client = $this->clientService->find($client_id);
        
        if ($client) {
            $client_id = $client->client_id;
            
            if (!empty($user_id)) {
                // Existing user - save the association
                $existing = $this->userClientService->getByUserAndClient($user_id, $client_id);
                if (!$existing) {
                    $this->userClientService->create([
                        'user_id' => $user_id,
                        'client_id' => $client_id,
                    ]);
                }
            } else {
                // New user - store in session until user is created
                $user_clients = Session::get('user_clients', []);
                $user_clients[$client_id] = $client_id;
                Session::put('user_clients', $user_clients);
            }
        }
    }

    /**
     * Load user-client table partial (AJAX endpoint).
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function loadUserClientTable
     * @legacy-file application/modules/users/controllers/Ajax.php
     */
    public function loadUserClientTable(): \Illuminate\View\View
    {
        $session_user_clients = Session::get('user_clients');
        
        if ($session_user_clients) {
            $clients = $this->clientService->getByIds(array_values($session_user_clients));
            return view('core::users_partial_user_client_table', [
                'id' => null,
                'user_clients' => $clients,
            ]);
        }
        
        $user_id = request()->input('user_id');
        $user_clients = $this->userClientService->getByUserId($user_id);
        
        return view('core::users_partial_user_client_table', [
            'id' => $user_id,
            'user_clients' => $user_clients,
        ]);
    }

    /**
     * Show modal for adding user-client association (AJAX endpoint).
     *
     * @param int|null $user_id User ID
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modalAddUserClient
     * @legacy-file application/modules/users/controllers/Ajax.php
     */
    public function modalAddUserClient(?int $user_id = null): \Illuminate\View\View
    {
        $session_user_clients = Session::get('user_clients');
        
        if ($session_user_clients) {
            $clients = $this->clientService->getNotInIds(array_values($session_user_clients));
        } else {
            $assigned_clients = $this->userClientService->getByUserId($user_id);
            $assigned_client_ids = $assigned_clients->pluck('client_id')->toArray();
            
            if (empty($assigned_client_ids)) {
                $clients = $this->clientService->getActiveClients();
            } else {
                $clients = $this->clientService->getNotInIds($assigned_client_ids);
            }
        }

        return view('core::users_modal_user_client', [
            'user_id' => $user_id,
            'clients' => $clients,
        ]);
    }
}
