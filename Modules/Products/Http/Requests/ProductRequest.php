<?php

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductRequest.
 *
 * Form request for validating product create and update operations
 */
class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
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
