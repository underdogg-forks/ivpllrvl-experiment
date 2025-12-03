<?php

namespace Modules\Crm\Services;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Core\Services\BaseService;
use Modules\Crm\Models\Client;
use Modules\Crm\Models\UserClient;

/**
 * UserClientService.
 *
 * Service class for managing user-client relationship business logic
 */
class UserClientService extends BaseService
{
    /**
     * Get all user clients paginated with relationships.
     *
     * @param int $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated(int $page = 0): LengthAwarePaginator
    {
        $page = max(1, $page);

        return UserClient::query()
            ->with(['user', 'client'])
            ->paginate(15, ['*'], 'page', $page);
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
     * @param array $data Data to validate
     *
     * @return bool Returns true if validation passes
     *
     * @throws InvalidArgumentException When validation fails
     *
     * @legacy-function runValidation
     */
    public function validate(array $data): bool
    {
        $errors = [];

        if (empty($data['user_id']) || ! is_numeric($data['user_id'])) {
            $errors[] = 'User ID is required and must be a valid integer';
        }

        if (empty($data['client_id']) || ! is_numeric($data['client_id'])) {
            $errors[] = 'Client ID is required and must be a valid integer';
        }

        if ( ! empty($data['user_id']) && is_numeric($data['user_id'])) {
            $userExists = DB::table('ip_users')
                ->where('user_id', $data['user_id'])
                ->exists();

            if ( ! $userExists) {
                $errors[] = 'User with ID ' . $data['user_id'] . ' does not exist';
            }
        }

        if ( ! empty($data['client_id']) && is_numeric($data['client_id'])) {
            $clientExists = DB::table('ip_clients')
                ->where('client_id', $data['client_id'])
                ->exists();

            if ( ! $clientExists) {
                $errors[] = 'Client with ID ' . $data['client_id'] . ' does not exist';
            }
        }

        if ( ! empty($data['user_id']) && ! empty($data['client_id'])) {
            $existingAssignment = UserClient::query()
                ->where('user_id', $data['user_id'])
                ->where('client_id', $data['client_id']);

            if ( ! empty($data['user_client_id'])) {
                $existingAssignment->where('user_client_id', '!=', $data['user_client_id']);
            }

            if ($existingAssignment->exists()) {
                $errors[] = 'This user is already assigned to this client';
            }
        }

        if ( ! empty($errors)) {
            throw new InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        return true;
    }

    /**
     * @param $user_id
     *
     * @return $this
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/user_clients/models/Mdl_user_client.php
     *
     * @legacy-function assigned_to()
     */
    public function assigned_to($user_id)
    {
        return UserClient::query()
            ->where('user_id', $user_id)
            ->with('client')
            ->get();
    }

    /**
     * Set all clients for a user.
     *
     * @param array $userIds Array of user IDs
     *
     * @legacy-file application/modules/user_clients/models/Mdl_user_client.php
     *
     * @legacy-function set_all_clients_user()
     *
     * @return void
     */
    public function setAllClientsUser(array $userIds): void
    {
        $userIds = array_values(array_filter($userIds, fn ($id) => is_numeric($id) && $id > 0));
        if (empty($userIds)) {
            return;
        }

        $clientIds = Client::query()->pluck('client_id')->all();
        if (empty($clientIds)) {
            return;
        }

        DB::transaction(function () use ($userIds, $clientIds) {
            foreach ($userIds as $userId) {
                $existing = UserClient::query()
                    ->where('user_id', $userId)
                    ->pluck('client_id')
                    ->all();

                $toInsert = array_diff($clientIds, $existing);

                if (empty($toInsert)) {
                    continue;
                }

                $rows = array_map(function ($clientId) use ($userId) {
                    return [
                        'user_id'   => (int) $userId,
                        'client_id' => (int) $clientId,
                    ];
                }, $toInsert);

                DB::table('ip_user_clients')->insert($rows);
            }
        });
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/user_clients/models/Mdl_user_client.php
     *
     * @legacy-function get_users_all_clients()
     */
    public function get_users_all_clients()
    {
        $userIds = DB::table('ip_users')
            ->where('user_all_clients', 1)
            ->pluck('user_id')
            ->all();

        if (empty($userIds)) {
            return;
        }

        $this->setAllClientsUser($userIds);
    }

    /**
     * Save user client assignment.
     *
     * @param array $data Assignment data to save
     *
     * @return UserClient The saved user client assignment
     *
     * @throws Exception When save operation fails
     *
     * @legacy-function save
     */
    public function save(array $data): UserClient
    {
        $this->validate($data);

        try {
            DB::beginTransaction();

            if ( ! empty($data['user_client_id'])) {
                $userClient = $this->findOrFail($data['user_client_id']);
                $userClient->fill($data);
                $userClient->save();
            } else {
                $userClient = UserClient::create($data);
            }

            DB::commit();

            return $userClient;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to save user client assignment: ' . $e->getMessage());
        }
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return UserClient::class;
    }
}
