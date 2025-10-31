<?php

namespace Modules\Products\Services;

use Modules\Products\Models\Product;

/**
 * ProductService.
 *
 * Service class for managing product business logic
 */
class ProductService
{
    /**
     * Get validation rules for products.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'product_name'        => 'required|string|max:255',
            'product_sku'         => 'nullable|string|max:255',
            'product_description' => 'nullable|string',
            'product_price'       => 'nullable|numeric|min:0',
            'purchase_price'      => 'nullable|numeric|min:0',
            'provider_name'       => 'nullable|string|max:255',
            'family_id'           => 'nullable|integer',
            'tax_rate_id'         => 'nullable|integer',
            'unit_id'             => 'nullable|integer',
            'product_tariff'      => 'nullable|string',
        ];
    }
}
