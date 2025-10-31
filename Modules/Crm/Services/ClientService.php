<?php

namespace Modules\Crm\Services;

use App\Services\BaseService;
use Modules\Crm\Models\Client;

/**
 * ClientService.
 *
 * Service class for managing client business logic
 */
class ClientService extends BaseService
{
    protected function getModelClass(): string
    {
        return Client::class;
    }
}
