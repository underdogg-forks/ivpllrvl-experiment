<?php

namespace Modules\Invoices\Services;

class InvoicesRecurringService
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
}
