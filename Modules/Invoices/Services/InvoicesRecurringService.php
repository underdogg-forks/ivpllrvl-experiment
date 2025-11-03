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
}
