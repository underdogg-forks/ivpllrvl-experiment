<?php

namespace Modules\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProjectRequest.
 *
 * Form request for validating project create and update operations
 */
class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'      => 'required|integer',
            'project_name'   => 'required|string|max:255',
            'project_status' => 'nullable|integer',
        ];
    }
}
