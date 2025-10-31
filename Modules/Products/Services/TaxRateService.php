<?php

namespace Modules\Products\Services;

/**
 * TaxRateService.
 *
 * Service class for managing tax rate business logic
 */
class TaxRateService
{
    /**
     * Get validation rules for tax rates.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'tax_rate_name'    => 'required|string|max:255',
            'tax_rate_percent' => 'required|numeric|min:0|max:100',
        ];
    }
}
