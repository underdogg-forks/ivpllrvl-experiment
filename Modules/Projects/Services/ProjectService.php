<?php

namespace Modules\Projects\Services;

use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Project;

/**
 * ProjectService.
 *
 * Service class for managing project business logic
 */
class ProjectService extends BaseService
{
    protected function getModelClass(): string
    {
        return Project::class;
    }

    /**
     * Get all projects ordered by name.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllOrdered(): \Illuminate\Database\Eloquent\Collection
    {
        return Project::query()->orderBy('project_name')->get();
    }
}
