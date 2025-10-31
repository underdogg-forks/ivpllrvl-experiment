<?php

namespace Modules\Payments\Services;

/**
 * PaymentService.
 *
 * Service class for managing payment business logic
 */
class PaymentService
{
    /**
     * Get validation rules for payments.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'invoice_id'         => 'required|integer',
            'payment_method_id'  => 'required|integer',
            'payment_amount'     => 'required|numeric|min:0',
            'payment_date'       => 'required|date',
            'payment_note'       => 'nullable|string',
        ];
    }
}
