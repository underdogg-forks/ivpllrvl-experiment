<?php

namespace Modules\Crm\Services;

use App\Services\BaseService;
use Modules\Crm\Models\Task;

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
