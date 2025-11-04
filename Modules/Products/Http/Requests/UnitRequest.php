<?php

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
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
        $unitId = $this->route('unit') ? $this->route('unit')->unit_id : null;

        return [
            'unit_name'      => 'required|string|max:255|unique:ip_units,unit_name' . ($unitId ? ',' . $unitId . ',unit_id' : ''),
            'unit_name_plrl' => 'required|string|max:255',
        ];
    }
}
