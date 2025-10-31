<?php

namespace Modules\Payments\Services;

/**
 * PaymentLogService.
 *
 * Service class for managing payment log business logic
 */
class PaymentLogService
{
    /**
     * Get validation rules for payment logs.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'payment_id'   => 'required|integer',
            'log_message'  => 'required|string',
            'log_date'     => 'required|date',
        ];
    }
}
