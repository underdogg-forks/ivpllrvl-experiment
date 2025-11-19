<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\User;
use Modules\Core\Services\UserService;
use Modules\Core\Support\TranslationHelper;
use Modules\Crm\Services\ClientService;
use Modules\Crm\Services\UserClientService;

/**
 * UserClientsController.
 *
 * Manages user-client relationship assignments
 *
 * @legacy-file application/modules/user_clients/controllers/User_clients.php
 */
class UserClientsController
{
    /**
     * Initialize the UserClientsController with dependency injection.
     *
     * @param UserService       $userService
     * @param UserClientService $userClientService
     * @param ClientService     $clientService
     */
    public function __construct(
        protected UserService $userService,
        protected UserClientService $userClientService,
        protected ClientService $clientService
    ) {}

    /**
     * Redirect to users index.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/user_clients/controllers/User_clients.php
     */
    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('users.index');
    }

    /**
     * Display user's client assignments.
     *
     * @param int|null $id User ID
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function user
     *
     * @legacy-file application/modules/user_clients/controllers/User_clients.php
     */
    public function user(?int $id = null)
    {
        if (request()->input('btn_cancel')) {
            return redirect()->route('users.index');
        }

        $user = $this->userService->find($id);
        if (empty($user)) {
            return redirect()->route('users.index');
        }

        $user_clients = $this->userClientService->getByUserId($id);

        return view('core::user_clients_field', [
            'user'         => $user,
            'user_clients' => $user_clients,
            'id'           => $id,
        ]);
    }

    /**
     * Create a new user-client assignment.
     *
     * @param int|null $user_id User ID
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function create
     *
     * @legacy-file application/modules/user_clients/controllers/User_clients.php
     */
    public function create(?int $user_id = null)
    {
        if ( ! $user_id) {
            return redirect()->route('custom_values.index');
        }

        if (request()->input('btn_cancel')) {
            return redirect()->route('user_clients.user', ['id' => $user_id]);
        }

        if ($this->userClientService->validate()) {
            if (request()->input('user_all_clients')) {
                $users_id = [$user_id];
                $this->userClientService->setAllClientsUser($users_id);
                $user_update = ['user_all_clients' => 1];
            } else {
                $user_update = ['user_all_clients' => 0];
                $this->userClientService->save();
            }

            User::query()->where('user_id', $user_id)->update($user_update);

            return redirect()->route('user_clients.user', ['id' => $user_id]);
        }

        $user    = $this->userService->find($user_id);
        $clients = $this->clientService->getNotAssignedToUser($user_id);

        return view('core::user_clients_create', [
            'id'      => $user_id,
            'user'    => $user,
            'clients' => $clients,
        ]);
    }

    /**
     * Delete a user-client assignment.
     *
     * @param int $user_client_id User-client relation ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     *
     * @legacy-file application/modules/user_clients/controllers/User_clients.php
     */
    public function delete(int $user_client_id): \Illuminate\Http\RedirectResponse
    {
        $ref = $this->userClientService->find($user_client_id);
        $this->userClientService->delete($user_client_id);

        return redirect()->route('user_clients.user', ['id' => $ref->user_id])
            ->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}
