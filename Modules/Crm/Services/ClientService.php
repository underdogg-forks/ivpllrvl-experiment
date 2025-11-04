<?php

namespace Modules\Crm\Services;

use Modules\Core\Services\BaseService;
use Modules\Crm\Models\Client;

/**
 * ClientService.
 *
 * Service class for managing client business logic
 */
class ClientService extends BaseService
{
    /**
     * Get all active clients ordered by name.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveClients()
    {
        return Client::query()->where('client_active', 1)->orderBy('client_name')->get();
    }

    /**
     * Get all clients ordered by name.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllOrderedByName()
    {
        return Client::query()->orderBy('client_name')->get();
    }

    /**
     * Get clients not assigned to a specific user.
     *
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @legacy-function getNotAssignedToUser
     */
    public function getNotAssignedToUser(int $userId)
    {
        // TODO: Implement logic to get clients not assigned to user
        return Client::query()
            ->whereNotIn('client_id', function ($query) use ($userId) {
                $query->select('client_id')
                    ->from('ip_user_clients')
                    ->where('user_id', $userId);
            })
            ->orderBy('client_name')
            ->get();
    }

    /**
     * Get clients by IDs.
     *
     * @param array $ids Client IDs
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @legacy-function getByIds
     */
    public function getByIds(array $ids)
    {
        return Client::query()
            ->whereIn('client_id', $ids)
            ->orderBy('client_name')
            ->get();
    }

    /**
     * Get clients not in given IDs.
     *
     * @param array $ids Client IDs to exclude
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @legacy-function getNotInIds
     */
    public function getNotInIds(array $ids)
    {
        return Client::query()
            ->whereNotIn('client_id', $ids)
            ->orderBy('client_name')
            ->get();
    }

    protected function getModelClass(): string
    {
        return Client::class;
    }
}
