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

class ClientService extends BaseService
{
    public function getActiveClients(): Collection
    {
        return Client::query()
            ->where('client_active', 1)
            ->orderBy('client_name')
            ->get();
    }

    public function getAllOrderedByName(): Collection
    {
        return Client::query()
            ->orderBy('client_name')
            ->get();
    }

    public function getNotAssignedToUser(int $userId): Collection
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

    public function remove(int $id): void
    {
        if (! Client::query()->where('client_id', $id)->exists()) {
            return;
        }

        if (! $this->canDelete($id)) {
            throw new RuntimeException('Client cannot be deleted because related records exist.');
        }

        DB::transaction(function () use ($id) {
            Client::query()->where('client_id', $id)->delete();
            $this->deleteOrphans($id);
        });
    }

    public function client_lookup(string $client_name): ?int
    {
        $client = Client::query()
            ->where('client_name', $client_name)
            ->first();

        if ($client) {
            return (int) $client->client_id;
        }

        $created = Client::query()->create([
            'client_name' => $client_name,
        ]);

        return $created ? (int) $created->client_id : null;
    }

    public function with_total(): Builder
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

    public function with_total_paid(): Builder
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

    public function with_total_balance(): Builder
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

    public function getByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return Client::query()
            ->whereIn('client_id', $ids)
            ->orderBy('client_name')
            ->get();
    }

    public function getNotInIds(array $ids): Collection
    {
        $query = Client::query()->orderBy('client_name');

        if (! empty($ids)) {
            $query->whereNotIn('client_id', $ids);
        }

        return $query->get();
    }

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

    private function deleteOrphans(int $clientId): void
    {
        DB::table('ip_user_clients')
            ->where('client_id', $clientId)
            ->delete();
    }
}
