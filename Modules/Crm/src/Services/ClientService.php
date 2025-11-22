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
    public function getActiveClients()
    {
        return Client::query()->where('client_active', 1)->orderBy('client_name')->get();
    }

    public function getAllOrderedByName()
    {
        return Client::query()->orderBy('client_name')->get();
    }

    /**
     * @legacy-file application/modules/clients/models/Mdl_client.php
     * @legacy-function get_not_assigned_to_user()
     */
    public function getNotAssignedToUser(int $userId)
    {
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
     * @param int $id
     * @legacy-file application/modules/clients/models/Mdl_client.php
     * @legacy-function delete()
     */
    public function remove($id): void
    {
        $client = Client::find($id);
        if ($client) {
            $client->delete();

            // Simulate legacy orphan deletion helper
            // delete_orphans() logic can be implemented if needed
        }
    }

    /**
     * Returns client_id of existing client.
     *
     * @param $client_name
     * @return int|null
     * @legacy-file application/modules/clients/models/Mdl_client.php
     * @legacy-function client_lookup()
     */
    public function client_lookup($client_name)
    {
        $client = Client::query()->where('client_name', $client_name)->first();

        if ($client) {
            return $client->client_id;
        }

        $newClient = Client::create([
            'client_name' => $client_name,
        ]);

        return $newClient->client_id;
    }

    /**
     * @legacy-file application/modules/clients/models/Mdl_client.php
     * @legacy-function with_total()
     */
    public function with_total()
    {
        return Client::query()
            ->selectRaw('*, IFNULL((SELECT SUM(invoice_total) FROM ip_invoice_amounts WHERE invoice_id IN (SELECT invoice_id FROM ip_invoices WHERE ip_invoices.client_id = ip_clients.client_id)), 0) AS client_invoice_total');
    }

    /**
     * @legacy-file application/modules/clients/models/Mdl_client.php
     * @legacy-function with_total_paid()
     */
    public function with_total_paid()
    {
        return Client::query()
            ->selectRaw('*, IFNULL((SELECT SUM(invoice_paid) FROM ip_invoice_amounts WHERE invoice_id IN (SELECT invoice_id FROM ip_invoices WHERE ip_invoices.client_id = ip_clients.client_id)), 0) AS client_invoice_paid');
    }

    /**
     * @legacy-file application/modules/clients/models/Mdl_client.php
     * @legacy-function with_total_balance()
     */
    public function with_total_balance()
    {
        return Client::query()
            ->selectRaw('*, IFNULL((SELECT SUM(invoice_balance) FROM ip_invoice_amounts WHERE invoice_id IN (SELECT invoice_id FROM ip_invoices WHERE ip_invoices.client_id = ip_clients.client_id)), 0) AS client_invoice_balance');
    }

    /**
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
     * @legacy-function getNotInIds
     */
    public function getNotInIds(array $ids)
    {
        return Client::query()
            ->whereNotIn('client_id', $ids)
            ->orderBy('client_name')
            ->get();
    }

    public function canDelete(int $clientId): bool
    {
        if (Invoice::query()->where('client_id', $clientId)->exists()) {
            return false;
        }

        if (Quote::query()->where('client_id', $clientId)->exists()) {
            return false;
        }

        return ! Project::query()->where('client_id', $clientId)->exists();
    }

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
