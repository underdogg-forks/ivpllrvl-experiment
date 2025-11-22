<?php

namespace Modules\Projects\Services;

use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Task;
use Modules\Projects\Models\Project;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Invoices\Models\Invoice;

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
     * @legacy-function get_latest()
     */
    public function get_latest()
    {
        return Task::query()->orderByDesc('id');
    }

    /**
     * @param string $match
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     * @legacy-function by_task()
     */
    public function by_task(string $match)
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
     * @legacy-function get_invoice_for_task()
     */
    public function get_invoice_for_task(int $task_id)
    {
        $item = InvoiceItem::query()->where('item_task_id', $task_id)->first();
        if (!$item) {
            return null;
        }
        return Invoice::query()->find($item->invoice_id);
    }

    /**
     * @param int $invoice_id
     *
     * @return \Illuminate\Support\Collection
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     * @legacy-function get_tasks_to_invoice()
     */
    public function get_tasks_to_invoice(int $invoice_id)
    {
        $result = collect();

        if (!$invoice_id) {
            return $result;
        }

        $result = $result->merge(Task::query()
            ->where('project_id', 0)
            ->where('task_status', 3)
            ->orderBy('task_finish_date')
            ->orderBy('task_name')
            ->get());

        $tasks = Task::query()
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->join('invoices', 'invoices.client_id', '=', 'projects.client_id')
            ->where('invoices.id', $invoice_id)
            ->where('tasks.task_status', 3)
            ->orderBy('tasks.task_finish_date')
            ->orderBy('projects.project_name')
            ->orderBy('tasks.task_name')
            ->select('tasks.*', 'projects.project_name')
            ->get();

        return $result->merge($tasks);
    }

    /**
     * @param int $invoice_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     * @legacy-function update_on_invoice_delete()
     */
    public function update_on_invoice_delete(int $invoice_id)
    {
        $tasks = Task::query()
            ->join('invoice_items', 'invoice_items.item_task_id', '=', 'tasks.id')
            ->where('invoice_items.invoice_id', $invoice_id)
            ->get();

        foreach ($tasks as $task) {
            $this->update_status(3, $task->id);
        }
    }

    /**
     * @param int $new_status
     * @param int $task_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     * @legacy-function update_status()
     */
    public function update_status(int $new_status, int $task_id)
    {
        if (isset($this->statuses()[$new_status])) {
            parent::save($task_id, ['task_status' => $new_status]);
        }
    }

    /**
     * @return array
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
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
     * @legacy-function update_on_project_delete()
     */
    public function update_on_project_delete(int $project_id)
    {
        $tasks = Task::query()->where('project_id', $project_id)->get();
        foreach ($tasks as $task) {
            parent::save($task->id, ['project_id' => null]);
        }
    }

    /**
     * Get all tasks with relationships, ordered and paginated.
     *
     * @param array $relations
     * @param int   $perPage
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['project', 'taxRate'], int $perPage = 15)
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
        return !InvoiceItem::query()->where('item_task_id', $taskId)->exists();
    }

    protected function getModelClass(): string
    {
        return Task::class;
    }
}
