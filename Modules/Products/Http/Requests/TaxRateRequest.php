<?php

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tax_rate_name'    => 'required|string|max:255',
            'tax_rate_percent' => 'required|numeric|min:0|max:100',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Standardize the tax rate percent (convert comma to dot for decimal)
        if ($this->has('tax_rate_percent')) {
            $value = $this->input('tax_rate_percent');
            if (function_exists('standardize_amount')) {
                $value = standardize_amount($value);
            } else {
                // Fallback: ensure dot as decimal separator
                $value = str_replace(',', '.', $value);
            }
            $this->merge([
                'tax_rate_percent' => $value,
            ]);
        }
    }
}
