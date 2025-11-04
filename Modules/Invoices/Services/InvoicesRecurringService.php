<?php

namespace Modules\Invoices\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\InvoicesRecurring;

class InvoicesRecurringService extends BaseService
{
    protected function getModelClass(): string
    {
        return InvoicesRecurring::class;
    }

    public function getValidationRules(): array
    {
        return [
            'invoice_id'       => 'required|integer',
            'recur_start_date' => 'required|date',
            'recur_end_date'   => 'nullable|date',
            'recur_frequency'  => 'required|string',
            'recur_next_date'  => 'nullable|date',
        ];
    }

    public function stopRecurring(int $recurringId): void
    {
        $this->update($recurringId, ['recur_status' => 0]);
    }

    /**
     * Get all recurring invoices with relationships.
     *
     * @param array $relations Relations to eager load
     * @param int $perPage Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['invoice'], int $perPage = 15)
    {
        return InvoicesRecurring::query()->with($relations)
            ->orderBy('recur_start_date', 'desc')
            ->paginate($perPage);
    }
}
