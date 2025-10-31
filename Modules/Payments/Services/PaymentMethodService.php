<?php

namespace Modules\Payments\Services;

/**
 * PaymentMethodService.
 *
 * Service class for managing payment method business logic
 */
class PaymentMethodService
{
    /**
     * Get validation rules for payment methods.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'payment_method_name' => 'required|string|max:255',
        ];
    }
}
