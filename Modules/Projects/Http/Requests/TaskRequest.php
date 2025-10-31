<?php

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * TaskRequest.
 *
 * Form request for validating task create and update operations
 */
class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'       => 'nullable|integer',
            'task_name'        => 'required|string|max:255',
            'task_status'      => 'nullable|integer',
            'task_finish_date' => 'nullable|date',
        ];
    }
}
