<?php

namespace Modules\Invoices\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\InvoicesRecurring;
use DateTime;

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
     * @param array $relations Relations to eager load
     * @param int   $perPage   Number of items per page
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
    public function stop($invoice_recurring_id): void
    {
        InvoicesRecurring::query()->where('invoice_recurring_id', $invoice_recurring_id)
            ->update([
                'recur_end_date'  => now()->format('Y-m-d'),
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
            ->where('recur_next_date', '<=', now()->format('Y-m-d'))
            ->where(function ($query) {
                $query->where('recur_end_date', '>', now()->format('Y-m-d'))
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
    public function set_next_recur_date($invoice_recurring_id): void
    {
        $invoiceRecurring = InvoicesRecurring::query()
            ->findOrFail($invoice_recurring_id);

        $recurNextDate = $this->incrementDate($invoiceRecurring->recur_next_date, $invoiceRecurring->recur_frequency);

        $invoiceRecurring->update(['recur_next_date' => $recurNextDate]);
    }

    /**
     * Increment a date string by a frequency string (legacy helper replacement)
     */
    protected function incrementDate(string $date, string $frequency): string
    {
        $dt = new DateTime($date);

        match ($frequency) {
            'daily'   => $dt->modify('+1 day'),
            'weekly'  => $dt->modify('+1 week'),
            'biweekly'=> $dt->modify('+2 weeks'),
            'monthly' => $dt->modify('+1 month'),
            'quarterly'=> $dt->modify('+3 months'),
            'yearly'  => $dt->modify('+1 year'),
            default   => $dt,
        };

        return $dt->format('Y-m-d');
    }

    protected function getModelClass(): string
    {
        return InvoicesRecurring::class;
    }
}
