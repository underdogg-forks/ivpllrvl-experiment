<?php

namespace Modules\Invoices\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\InvoicesRecurring;

class InvoicesRecurringService extends BaseService
{
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
     * @param array $relations
     * @param int   $perPage
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['invoice'], int $perPage = 15)
    {
        return InvoicesRecurring::query()->with($relations)
            ->orderBy('recur_start_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * @param $invoice_recurring_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice_recurring.php
     *
     * @legacy-function stop()
     */
    public function stop($invoice_recurring_id)
    {
        InvoicesRecurring::query()
            ->where('invoice_recurring_id', $invoice_recurring_id)
            ->update([
                'recur_end_date'  => now()->toDateString(),
                'recur_next_date' => null,
            ]);
    }

    /**
     * Sets filter to only recurring invoices which should be generated now.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice_recurring.php
     *
     * @legacy-function active()
     */
    public function active()
    {
        return InvoicesRecurring::query()
            ->where('recur_next_date', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->where('recur_end_date', '>', now()->toDateString())
                    ->orWhereNull('recur_end_date');
            });
    }

    /**
     * @param $invoice_recurring_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice_recurring.php
     *
     * @legacy-function set_next_recur_date()
     */
    public function set_next_recur_date($invoice_recurring_id)
    {
        $recurring = InvoicesRecurring::query()->find($invoice_recurring_id);

        if (!$recurring) {
            return;
        }

        $recur_next_date = increment_date($recurring->recur_next_date, $recurring->recur_frequency);

        $recurring->update(['recur_next_date' => $recur_next_date]);
    }

    protected function getModelClass(): string
    {
        return InvoicesRecurring::class;
    }
}
