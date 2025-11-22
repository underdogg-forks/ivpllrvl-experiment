<?php

namespace Modules\Projects\Services;

use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Task;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Illuminate\Support\Collection;

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
     * @param string $match
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function by_task()
     */
    public function by_task($match)
    {
        return Task::query()
            ->where('task_name', 'like', "%$match%")
            ->orWhere('task_description', 'like', "%$match%");
    }

    /**
     * @param int $task_id
     *
     * @return Invoice|null
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function get_invoice_for_task()
     */
    public function get_invoice_for_task($task_id)
    {
        if (!$task_id) {
            return null;
        }

        $invoiceItem = InvoiceItem::query()
            ->where('item_task_id', $task_id)
            ->first();

        if (!$invoiceItem) {
            return null;
        }

        return Invoice::query()->find($invoiceItem->invoice_id);
    }

    /**
     * @param int $invoice_id
     *
     * @return Collection
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function get_tasks_to_invoice()
     */
    public function get_tasks_to_invoice($invoice_id)
    {
        if (!$invoice_id) {
            return collect();
        }

        $tasks = Task::query()
            ->where('project_id', 0)
            ->where('task_status', 3)
            ->orderBy('task_finish_date')
            ->orderBy('task_name')
            ->get();

        $projectTasks = Task::query()
            ->select('tasks.*', 'projects.project_name')
            ->join('projects', 'projects.project_id', '=', 'tasks.project_id')
            ->join('invoices', 'invoices.client_id', '=', 'projects.client_id')
            ->where('invoices.invoice_id', $invoice_id)
            ->where('tasks.task_status', 3)
            ->orderBy('tasks.task_finish_date')
            ->orderBy('projects.project_name')
            ->orderBy('tasks.task_name')
            ->get();

        return $tasks->merge($projectTasks);
    }

    /**
     * @param int $invoice_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function update_on_invoice_delete()
     */
    public function update_on_invoice_delete($invoice_id)
    {
        if (!$invoice_id) {
            return;
        }

        $tasks = Task::query()
            ->join('invoice_items', 'invoice_items.item_task_id', '=', 'tasks.task_id')
            ->where('invoice_items.invoice_id', $invoice_id)
            ->get();

        foreach ($tasks as $task) {
            $this->update_status(3, $task->task_id);
        }
    }

    /**
     * @param int $new_status
     * @param int $task_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function update_status()
     */
    public function update_status($new_status, $task_id)
    {
        $statuses_ok = $this->statuses();
        if (isset($statuses_ok[$new_status])) {
            $this->save($task_id, ['task_status' => $new_status]);
        }
    }

    /**
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
            '1' => ['label' => trans('not_started'), 'class' => 'draft'],
            '2' => ['label' => trans('in_progress'), 'class' => 'viewed'],
            '3' => ['label' => trans('complete'), 'class' => 'sent'],
            '4' => ['label' => trans('invoiced'), 'class' => 'paid'],
        ];
    }

    /**
     * @param int $project_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function update_on_project_delete()
     */
    public function update_on_project_delete($project_id)
    {
        if (!$project_id) {
            return;
        }

        $tasks = Task::query()->where('project_id', $project_id)->get();

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
        : \Illuminate\Contracts\Pagination\LengthAwarePaginator
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
            return true;
        }

        return !$this->isAssignedToInvoice($taskId);
    }

    protected function getModelClass(): string
    {
        return Task::class;
    }
}
