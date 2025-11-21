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
    public function stop($invoice_recurring_id)
    {
/*
        $db_array = [
            'recur_end_date'  => date('Y-m-d'),
            'recur_next_date' => null,
        ];

        $this->db->where('invoice_recurring_id', $invoice_recurring_id);
        $this->db->update('ip_invoices_recurring', $db_array);
*/
    }

    /**
     * Sets filter to only recurring invoices which should be generated now.
     *
     * @return Mdl_Invoices_Recurring
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice_recurring.php
     *
     * @legacy-function active()
     */
    public function active()
    {
/*
        $this->filter_where('recur_next_date <= date(NOW()) AND (recur_end_date > date(NOW()) OR recur_end_date IS NULL)');

        return $this;
*/
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
/*
        $invoice_recurring = $this->where('invoice_recurring_id', $invoice_recurring_id)->get()->row();

        $recur_next_date = increment_date($invoice_recurring->recur_next_date, $invoice_recurring->recur_frequency);

        $db_array = [
            'recur_next_date' => $recur_next_date,
        ];

        $this->db->where('invoice_recurring_id', $invoice_recurring_id);
        $this->db->update('ip_invoices_recurring', $db_array);
*/
    }

    protected function getModelClass(): string
    {
        return InvoicesRecurring::class;
    }
}
