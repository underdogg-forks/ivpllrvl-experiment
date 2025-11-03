<?php

namespace Modules\Crm\Services;

use Modules\Core\Services\BaseService;
use Modules\Crm\Models\UserClient;

/**
 * UserClientService.
 *
 * Service class for managing user-client relationship business logic
 */
class UserClientService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return UserClient::class;
    }

    /**
     * Get all user clients paginated with relationships.
     *
     * @param int $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated(int $page = 0): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return UserClient::with(['user', 'client'])->paginate(15, ['*'], 'page', $page);
    }

    /**
     * Get user clients by user ID.
     *
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @legacy-function assignedTo
     */
    public function getByUserId(int $userId)
    {
        return UserClient::query()
            ->where('user_id', $userId)
            ->with('client')
            ->get();
    }

    /**
     * Get user client by user ID and client ID.
     *
     * @param int $userId
     * @param int $clientId
     *
     * @return UserClient|null
     *
     * @legacy-function getByUserAndClient
     */
    public function getByUserAndClient(int $userId, int $clientId): ?UserClient
    {
        return UserClient::query()
            ->where('user_id', $userId)
            ->where('client_id', $clientId)
            ->first();
    }

    /**
     * Validate user client assignment.
     *
     * @return bool
     *
     * @legacy-function runValidation
     */
    public function validate(): bool
    {
        // TODO: Implement validation logic
        return true;
    }

    /**
     * Set all clients for a user.
     *
     * @param array $userIds Array of user IDs
     *
     * @return void
     *
     * @legacy-function setAllClientsUser
     */
    public function setAllClientsUser(array $userIds): void
    {
        // TODO: Implement set all clients logic
    }

    /**
     * Save user client assignment.
     *
     * @return void
     *
     * @legacy-function save
     */
    public function save(): void
    {
        // TODO: Implement save logic
    }
}
