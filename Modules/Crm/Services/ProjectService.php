<?php

namespace Modules\Crm\Services;

/**
 * ProjectService.
 *
 * Service class for managing project business logic
 */
class ProjectService
{
    /**
     * Get validation rules for projects.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'client_id'      => 'required|integer',
            'project_name'   => 'required|string|max:255',
            'project_status' => 'nullable|integer',
        ];
    }
}
