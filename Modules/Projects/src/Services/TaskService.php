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
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function get_latest()
     */
    public function get_latest()
    {
/*
        $this->db->order_by('ip_tasks.task_id', 'DESC');

        return $this;
*/
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
/*
        $this->db->like('task_name', $match);
        $this->db->or_like('task_description', $match);
*/
    }



    /**
     * @param int $task_id
     *
     * @return array
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function get_invoice_for_task()
     */
    public function get_invoice_for_task($task_id)
    {
/*
        if ( ! $task_id) {
            return;
        }

        $invoice_item = $this->db->select('ip_invoice_items.invoice_id')
            ->from('ip_invoice_items')
            ->where('ip_invoice_items.item_task_id', $task_id)
            ->get()->result();

        if (empty($invoice_item) || ! isset($invoice_item->invoice_id)) {
            return;
        }

        $this->load->model('invoices/invoice');

        return $this->mdl_invoices->get_by_id($invoice_item->invoice_id);
*/
    }

    /**
     * @param int $invoice_id
     *
     * @return array
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/models/Mdl_task.php
     *
     * @legacy-function get_tasks_to_invoice()
     */
    public function get_tasks_to_invoice($invoice_id)
    {
/*
        $result = [];

        if ( ! $invoice_id) {
            return $result;
        }

        // Get tasks without any project
        $query = $this->db->select($this->table . '.*')
            ->from($this->table)
            ->where($this->table . '.project_id', 0)
            ->where($this->table . '.task_status', 3)
            ->order_by($this->table . '.task_finish_date', 'ASC')
            ->order_by($this->table . '.task_name', 'ASC')
            ->get();

        foreach ($query->result() as $row) {
            $result[] = $row;
        }

        // Get tasks for this invoice
        $query = $this->db->select($this->table . '.*, ip_projects.project_name')
            ->from($this->table)
            ->join('ip_projects', 'ip_projects.project_id = ' . $this->table . '.project_id')
            ->join('ip_invoices', 'ip_invoices.client_id = ip_projects.client_id')
            ->where('ip_invoices.invoice_id', $invoice_id)
            ->where($this->table . '.task_status', 3)
            ->order_by($this->table . '.task_finish_date', 'ASC')
            ->order_by('ip_projects.project_name', 'ASC')
            ->order_by($this->table . '.task_name', 'ASC')
            ->get();

        foreach ($query->result() as $row) {
            $result[] = $row;
        }

        return $result;
*/
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
/*
        if ( ! $invoice_id) {
            return;
        }

        $query = $this->db->select($this->table . '.*')
            ->from($this->table)
            ->join('ip_invoice_items', 'ip_invoice_items.item_task_id = ' . $this->table . '.task_id')
            ->where('ip_invoice_items.invoice_id', $invoice_id)
            ->get();

        foreach ($query->result() as $task) {
            $this->update_status(3, $task->task_id);
        }
*/
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
/*
        $statuses_ok = $this->statuses();
        if (isset($statuses_ok[$new_status])) {
            parent::save($task_id, ['task_status' => $new_status]);
        }
*/
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
    public function statuses()
    {
        return [
            '1' => [
                'label' => trans('not_started'),
                'class' => 'draft',
            ],
            '2' => [
                'label' => trans('in_progress'),
                'class' => 'viewed',
            ],
            '3' => [
                'label' => trans('complete'),
                'class' => 'sent',
            ],
            '4' => [
                'label' => trans('invoiced'),
                'class' => 'paid',
            ],
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
/*
        if ( ! $project_id) {
            return;
        }

        $query = $this->db->select($this->table . '.*')
            ->from($this->table)
            ->where($this->table . '.project_id', $project_id)
            ->get();

        foreach ($query->result() as $task) {
            parent::save($task->task_id, ['project_id' => null]);
        }
*/
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
        // Reuse existing method
        $task = Task::query()->find($taskId);
        if ( ! $task) {
            return true;
        }

        return ! $this->isAssignedToInvoice($taskId);
    }

    protected function getModelClass(): string
    {
        return Task::class;
    }
}
