<?php

namespace Modules\Projects\Services;

use App\Services\BaseService;
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
}
