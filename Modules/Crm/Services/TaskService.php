<?php

namespace Modules\Crm\Services;

/**
 * TaskService.
 *
 * Service class for managing task business logic
 */
class TaskService
{
    /**
     * Get validation rules for tasks.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'project_id'     => 'nullable|integer',
            'task_name'      => 'required|string|max:255',
            'task_status'    => 'nullable|integer',
            'task_finish_date' => 'nullable|date',
        ];
    }
}
