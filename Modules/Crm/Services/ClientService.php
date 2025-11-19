<?php

namespace Modules\Crm\Services;

use Modules\Core\Services\BaseService;
use Modules\Crm\Models\Client;
use Modules\Invoices\Models\Invoice;
use Modules\Projects\Models\Project;
use Modules\Quotes\Models\Quote;

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

    /**
     * Check if a client can be deleted.
     *
     * A client cannot be deleted if it has:
     * - Invoices
     * - Quotes
     * - Projects
     *
     * @param int $clientId
     *
     * @return bool
     */
    public function canDelete(int $clientId): bool
    {
        // Check for invoices
        $invoiceCount = Invoice::query()->where('client_id', $clientId)->count();
        if ($invoiceCount > 0) {
            return false;
        }

        // Check for quotes
        $quoteCount = Quote::query()->where('client_id', $clientId)->count();
        if ($quoteCount > 0) {
            return false;
        }

        // Check for projects
        $projectCount = Project::query()->where('client_id', $clientId)->count();

        return ! ($projectCount > 0);
    }

    /**
     * Get deletion blocker details for a client.
     *
     * Returns an array with counts of related records.
     *
     * @param int $clientId
     *
     * @return array
     */
    public function getDeletionBlockers(int $clientId): array
    {
        return [
            'invoices' => Invoice::query()->where('client_id', $clientId)->count(),
            'quotes'   => Quote::query()->where('client_id', $clientId)->count(),
            'projects' => Project::query()->where('client_id', $clientId)->count(),
        ];
    }

    protected function getModelClass(): string
    {
        return Client::class;
    }
}
