<?php

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FamilyRequest extends FormRequest
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
        $familyId = $this->route('family') ? $this->route('family')->family_id : null;
        
        return [
            'family_name' => 'required|string|max:255|unique:ip_families,family_name' . ($familyId ? ',' . $familyId . ',family_id' : ''),
        ];
    }
}
