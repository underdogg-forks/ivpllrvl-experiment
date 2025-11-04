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

    protected function getModelClass(): string
    {
        return Task::class;
    }
}
