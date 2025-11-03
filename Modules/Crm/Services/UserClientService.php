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
}
