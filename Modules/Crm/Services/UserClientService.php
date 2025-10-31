<?php

namespace Modules\Crm\Services;

use App\Services\BaseService;
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
}
