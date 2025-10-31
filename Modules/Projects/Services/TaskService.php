<?php

namespace Modules\Projects\Services;

use App\Services\BaseService;
use Modules\Projects\Models\Task;

/**
 * TaskService.
 *
 * Service class for managing task business logic
 */
class TaskService extends BaseService
{
    protected function getModelClass(): string
    {
        return Task::class;
    }
}
