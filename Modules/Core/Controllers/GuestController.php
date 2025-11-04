<?php

namespace Modules\Core\Controllers;

use Modules\Core\Services\UserClientService;

/**
 * GuestController.
 *
 * Base controller for guest user operations
 *
 * @legacy-file application/modules/guest/controllers/Guest.php
 */
class GuestController
{
    protected array $userClients = [];

    protected UserClientService $userClientService;

    /**
     * Initialize guest controller and verify user has client access.
     *
     * @legacy-function __construct
     *
     * @legacy-file application/modules/guest/controllers/Guest.php
     */
    public function __construct(UserClientService $userClientService)
    {
        $this->userClientService = $userClientService;

        // Verify guest user has assigned clients
        $userId = session('user_id');
        if ( ! $userId) {
            abort(403, trans('guest_account_denied'));
        }

        $userClients = $this->userClientService->getAssignedClients($userId);

        if ($userClients->isEmpty()) {
            abort(403, trans('guest_account_denied'));
        }

        foreach ($userClients as $userClient) {
            $this->userClients[$userClient->client_id] = $userClient->client_id;
        }
    }

    /**
     * Get list of client IDs assigned to this guest user.
     *
     * @return array
     */
    protected function getUserClients(): array
    {
        return $this->userClients;
    }
}
