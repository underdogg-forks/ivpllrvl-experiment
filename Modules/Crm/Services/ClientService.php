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

    /**
     * Get all active clients ordered by name.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveClients()
    {
        return Client::where('client_active', 1)->orderBy('client_name')->get();
    }
}
