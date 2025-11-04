<?php

namespace Modules\Projects\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Support\SettingsHelper;
use Modules\Projects\Services\TaskService;
use Modules\Projects\Models\Task;

/**
 * TasksAjaxController
 *
 * Handles AJAX requests for task-related operations
 *
 * @legacy-file application/modules/tasks/controllers/Ajax.php
 */
class TasksAjaxController
{
    /**
     * Initialize the TasksAjaxController with dependency injection.
     *
     * @param TaskService $taskService
     */
    public function __construct(
        protected TaskService $taskService
    ) {
    }
    /**
     * Display modal for task lookups (AJAX endpoint).
     *
     * @param int|null $invoice_id Invoice ID to fetch tasks for
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @legacy-function modalTaskLookups
     * @legacy-file application/modules/tasks/controllers/Ajax.php
     */
    public function modalTaskLookups(?int $invoice_id = null): \Illuminate\Contracts\View\View
    {
        $default_item_tax_rate = SettingsHelper::getSetting('default_item_tax_rate');
        $data = [
            'default_item_tax_rate' => $default_item_tax_rate !== '' ? $default_item_tax_rate : 0,
            'tasks' => [],
        ];

        if (!empty($invoice_id)) {
            $data['tasks'] = $this->taskService->getTasksToInvoice($invoice_id);
        }

        return view('projects::tasks_modal_task_lookups', $data);
    }

    /**
     * Process task selections (AJAX endpoint).
     *
     * @param Request $request
     *
     * @return void Outputs JSON response
     *
     * @legacy-function processTaskSelections
     * @legacy-file application/modules/tasks/controllers/Ajax.php
     */
    public function processTaskSelections(Request $request): void
    {
        $taskIds = $request->input('task_ids', []);
        $tasks = Task::query()->whereIn('task_id', $taskIds)->get();

        foreach ($tasks as $task) {
            $task->task_price = format_amount($task->task_price);
        }

        header('Content-Type: application/json');
        echo json_encode($tasks);
    }
}
