<?php

namespace Modules\Projects\Services;

use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Task;

/**
 * TaskService.
 *
 * Service class for managing task business logic
 */
class TaskService extends BaseService
{
    /**
     * Update tasks by invoice ID.
     *
     * @param int   $invoiceId
     * @param array $data
     *
     * @return int
     */
    public function updateByInvoiceId(int $invoiceId, array $data): int
    {
        return $this->query()->where('invoice_id', $invoiceId)->update($data);
    }

    /**
     * Get all tasks with relationships, ordered and paginated.
     *
     * @param array $relations Relations to eager load
     * @param int   $perPage   Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['project', 'taxRate'], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Task::query()->with($relations)
            ->orderBy('task_name')
            ->paginate($perPage);
    }

    /**
     * Check if a task can be deleted.
     *
     * A task cannot be deleted if it is referenced by an invoice.
     *
     * @param int $taskId
     *
     * @return bool
     */
    public function canDelete(int $taskId): bool
    {
        $task = Task::query()->find($taskId);
        
        if (!$task) {
            return true; // Task doesn't exist, can "delete"
        }

        // Check if task has an invoice_id (is assigned to an invoice)
        return $task->invoice_id === null;
    }

    /**
     * Check if task is assigned to an invoice.
     *
     * @param int $taskId
     *
     * @return bool
     */
    public function isAssignedToInvoice(int $taskId): bool
    {
        $task = Task::query()->find($taskId);
        
        return $task && $task->invoice_id !== null;
    }

    protected function getModelClass(): string
    {
        return Task::class;
    }
}

