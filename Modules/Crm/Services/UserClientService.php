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
     * @param array $data Data to validate
     *
     * @return bool Returns true if validation passes
     *
     * @throws \InvalidArgumentException When validation fails
     *
     * @legacy-function runValidation
     */
    public function validate(array $data): bool
    {
        $errors = [];
        
        // Validate user_id exists and is an integer
        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            $errors[] = 'User ID is required and must be a valid integer';
        }
        
        // Validate client_id exists and is an integer
        if (empty($data['client_id']) || !is_numeric($data['client_id'])) {
            $errors[] = 'Client ID is required and must be a valid integer';
        }
        
        // Check if user and client exist (basic validation)
        if (!empty($data['user_id']) && is_numeric($data['user_id'])) {
            $userExists = \Illuminate\Support\Facades\DB::table('ip_users')
                ->where('user_id', $data['user_id'])
                ->exists();
            if (!$userExists) {
                $errors[] = 'User with ID ' . $data['user_id'] . ' does not exist';
            }
        }
        
        if (!empty($data['client_id']) && is_numeric($data['client_id'])) {
            $clientExists = \Illuminate\Support\Facades\DB::table('ip_clients')
                ->where('client_id', $data['client_id'])
                ->exists();
            if (!$clientExists) {
                $errors[] = 'Client with ID ' . $data['client_id'] . ' does not exist';
            }
        }
        
        // Check for duplicate assignment (user can't be assigned to same client twice)
        if (!empty($data['user_id']) && !empty($data['client_id'])) {
            $existingAssignment = UserClient::query()
                ->where('user_id', $data['user_id'])
                ->where('client_id', $data['client_id']);
            
            // If updating, exclude the current record
            if (!empty($data['user_client_id'])) {
                $existingAssignment->where('user_client_id', '!=', $data['user_client_id']);
            }
            
            if ($existingAssignment->exists()) {
                $errors[] = 'This user is already assigned to this client';
            }
        }
        
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }
        
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
     * @param array $data Assignment data to save
     *
     * @return UserClient The saved user client assignment
     *
     * @throws \Exception When save operation fails
     *
     * @legacy-function save
     */
    public function save(array $data): UserClient
    {
        // Validate input
        $this->validate($data);
        
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            
            // If user_client_id is present, update existing assignment
            if (!empty($data['user_client_id'])) {
                $userClient = $this->findOrFail($data['user_client_id']);
                $userClient->update($data);
            } else {
                // Create new assignment
                $userClient = $this->create($data);
            }
            
            \Illuminate\Support\Facades\DB::commit();
            
            return $userClient;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw new \Exception('Failed to save user client assignment: ' . $e->getMessage());
        }
    }
}
