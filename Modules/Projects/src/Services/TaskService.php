<?php

namespace Modules\Projects\Services;

use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Task;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;

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
     * Get latest tasks (ordered by descending ID).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function get_latest()
     */
    public function get_latest()
    {
        return Task::query()->orderByDesc('task_id');
    }

    /**
     * Search tasks by name or description.
     *
     * @param string $match
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function by_task()
     */
    public function by_task(string $match)
    {
        return Task::query()
            ->where('task_name', 'like', "%{$match}%")
            ->orWhere('task_description', 'like', "%{$match}%");
    }

    /**
     * Get invoice for a specific task.
     *
     * @param int $taskId
     *
     * @return Invoice|null
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function get_invoice_for_task()
     */
    public function get_invoice_for_task(int $taskId): ?Invoice
    {
        if (! $taskId) {
            return null;
        }

        $invoiceItem = InvoiceItem::query()
            ->where('item_task_id', $taskId)
            ->first();

        if (! $invoiceItem) {
            return null;
        }

        return Invoice::query()->find($invoiceItem->invoice_id);
    }

    /**
     * Get tasks eligible for invoicing for a given invoice.
     *
     * @param int $invoiceId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function get_tasks_to_invoice()
     */
    public function get_tasks_to_invoice(int $invoiceId)
    {
        if (! $invoiceId) {
            return collect();
        }

        $tasks = Task::query()
            ->where('project_id', 0)
            ->where('task_status', 3)
            ->orderBy('task_finish_date')
            ->orderBy('task_name')
            ->get();

        $projectTasks = Task::query()
            ->select('tasks.*')
            ->join('projects', 'projects.project_id', '=', 'tasks.project_id')
            ->join('invoices', 'invoices.client_id', '=', 'projects.client_id')
            ->where('invoices.invoice_id', $invoiceId)
            ->where('tasks.task_status', 3)
            ->orderBy('tasks.task_finish_date')
            ->orderBy('projects.project_name')
            ->orderBy('tasks.task_name')
            ->get();

        return $tasks->concat($projectTasks);
    }

    /**
     * Reset tasks when an invoice is deleted.
     *
     * @param int $invoiceId
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function update_on_invoice_delete()
     */
    public function update_on_invoice_delete(int $invoiceId): void
    {
        if (! $invoiceId) {
            return;
        }

        $tasks = Task::query()
            ->whereHas('invoiceItems', fn($q) => $q->where('invoice_id', $invoiceId))
            ->get();

        foreach ($tasks as $task) {
            $this->update_status(3, $task->task_id);
        }
    }

    /**
     * Update the status of a task.
     *
     * @param int $newStatus
     * @param int $taskId
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function update_status()
     */
    public function update_status(int $newStatus, int $taskId): void
    {
        if (isset($this->statuses()[$newStatus])) {
            $this->save($taskId, ['task_status' => $newStatus]);
        }
    }

    /**
     * Get all possible task statuses.
     *
     * @return array
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function statuses()
     */
    public function statuses(): array
    {
        return [
            1 => ['label' => trans('not_started'), 'class' => 'draft'],
            2 => ['label' => trans('in_progress'), 'class' => 'viewed'],
            3 => ['label' => trans('complete'), 'class' => 'sent'],
            4 => ['label' => trans('invoiced'), 'class' => 'paid'],
        ];
    }

    /**
     * Reset tasks when a project is deleted.
     *
     * @param int $projectId
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function update_on_project_delete()
     */
    public function update_on_project_delete(int $projectId): void
    {
        if (! $projectId) {
            return;
        }

        $tasks = Task::query()
            ->where('project_id', $projectId)
            ->get();

        foreach ($tasks as $task) {
            $this->save($task->task_id, ['project_id' => null]);
        }
    }

    /**
     * Get all tasks with relationships, ordered and paginated.
     *
     * @param array $relations Relations to eager load
     * @param int   $perPage   Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['project', 'taxRate'], int $perPage = 15)
    {
        return Task::query()
            ->with($relations)
            ->orderBy('task_name')
            ->paginate($perPage);
    }

    /**
     * Check if a task can be deleted (not referenced by invoices).
     *
     * @param int $taskId
     *
     * @return bool
     */
    public function canDelete(int $taskId): bool
    {
        $task = Task::query()->find($taskId);
        if (! $task) {
            return true;
        }

        return ! $this->isAssignedToInvoice($taskId);
    }

    /**
     * Check if task is assigned to any invoice items.
     */
    protected function isAssignedToInvoice(int $taskId): bool
    {
        return InvoiceItem::query()->where('item_task_id', $taskId)->exists();
    }

    protected function getModelClass(): string
    {
        return Task::class;
    }
}
