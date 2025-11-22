<?php

namespace Modules\Crm\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Crm\Models\Client;
use Modules\Invoices\Models\Invoice;
use Modules\Projects\Models\Project;
use Modules\Quotes\Models\Quote;
use RuntimeException;

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
        return Client::query()
            ->where('client_active', 1)
            ->orderBy('client_name')
            ->get();
    }

    /**
     * Get all clients ordered by name.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllOrderedByName()
    {
        return Client::query()
            ->orderBy('client_name')
            ->get();
    }

    /**
     * Get clients not assigned to a specific user.
     *
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/clients/models/Mdl_client.php
     *
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
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/clients/models/Mdl_client.php
     *
     * @legacy-function delete()
     */
    public function remove($id): void
    {
        $client = Client::query()->where('client_id', $id)->first();
        if (! $client) {
            return;
        }

        if (! $this->canDelete((int) $id)) {
            throw new RuntimeException('Client cannot be deleted because related records exist.');
        }

        DB::transaction(function () use ($id) {
            Client::query()->where('client_id', $id)->delete();
            DB::table('ip_user_clients')->where('client_id', $id)->delete();
            DB::table('ip_contacts')->where('client_id', $id)->delete();
        });
    }

    /**
     * Returns client_id of existing client.
     *
     * @param $client_name
     *
     * @return int|null
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/clients/models/Mdl_client.php
     *
     * @legacy-function client_lookup()
     */
    public function client_lookup($client_name)
    {
        $client = Client::query()
            ->where('client_name', $client_name)
            ->first();

        if ($client) {
            return (int) $client->client_id;
        }

        $created = Client::create([
            'client_name' => $client_name,
        ]);

        return $created ? (int) $created->client_id : null;
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/clients/models/Mdl_client.php
     *
     * @legacy-function with_total()
     */
    public function with_total()
    {
        $sub = DB::table('ip_invoice_amounts')
            ->selectRaw('COALESCE(SUM(invoice_total), 0)')
            ->whereIn('invoice_id', function ($q) {
                $q->select('invoice_id')
                    ->from('ip_invoices')
                    ->whereColumn('ip_invoices.client_id', 'ip_clients.client_id');
            });

        return Client::query()
            ->select('ip_clients.*')
            ->selectSub($sub, 'client_invoice_total');
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/clients/models/Mdl_client.php
     *
     * @legacy-function with_total_paid()
     */
    public function with_total_paid()
    {
        $sub = DB::table('ip_invoice_amounts')
            ->selectRaw('COALESCE(SUM(invoice_paid), 0)')
            ->whereIn('invoice_id', function ($q) {
                $q->select('invoice_id')
                    ->from('ip_invoices')
                    ->whereColumn('ip_invoices.client_id', 'ip_clients.client_id');
            });

        return Client::query()
            ->select('ip_clients.*')
            ->selectSub($sub, 'client_invoice_paid');
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/clients/models/Mdl_client.php
     *
     * @legacy-function with_total_balance()
     */
    public function with_total_balance()
    {
        $sub = DB::table('ip_invoice_amounts')
            ->selectRaw('COALESCE(SUM(invoice_balance), 0)')
            ->whereIn('invoice_id', function ($q) {
                $q->select('invoice_id')
                    ->from('ip_invoices')
                    ->whereColumn('ip_invoices.client_id', 'ip_clients.client_id');
            });

        return Client::query()
            ->select('ip_clients.*')
            ->selectSub($sub, 'client_invoice_balance');
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
        if (empty($ids)) {
            return collect();
        }

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
        $query = Client::query()->orderBy('client_name');

        if (! empty($ids)) {
            $query->whereNotIn('client_id', $ids);
        }

        return $query->get();
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
        if (Invoice::query()->where('client_id', $clientId)->exists()) {
            return false;
        }

        if (Quote::query()->where('client_id', $clientId)->exists()) {
            return false;
        }

        if (Project::query()->where('client_id', $clientId)->exists()) {
            return false;
        }

        return true;
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
