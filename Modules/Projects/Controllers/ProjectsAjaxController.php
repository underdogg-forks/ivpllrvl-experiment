<?php

namespace Modules\Projects\app\Http\Controllers;

use AllowDynamicProperties;
use Illuminate\Http\Request;
use Modules\Core\Controllers\AdminController;
use Modules\Projects\app\Services\TasksService;

use function Modules\Tasks\Controllers\view;

#[AllowDynamicProperties]
class ProjectsAjaxController extends AdminController
{
    /**
     * Render the task lookups modal populated with default tax rate and invoice tasks.
     *
     * Prepares view data containing the default item tax rate (0 when not set) and, if an
     * invoice ID is provided, the tasks associated with that invoice.
     *
     * @param int|null $invoice_id the invoice ID to fetch tasks for, or null to omit tasks
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory the rendered task lookups view
     */
    public function modalTaskLookups($invoice_id = null)
    {
        $default_item_tax_rate = get_setting('default_item_tax_rate');
        $data                  = ['default_item_tax_rate' => $default_item_tax_rate !== '' ?: 0, 'tasks' => []];
        if ( ! empty($invoice_id)) {
            $data['tasks'] = (new TasksService())->getTasksToInvoice($invoice_id);
        }

        return view('tasks.modal_task_lookups', $data);
    }

    /**
     * Outputs the selected tasks as a JSON array with each task's price formatted for display.
     *
     * Reads `task_ids` from the request, retrieves matching tasks, formats each task's `task_price`,
     * and writes the resulting task collection as JSON to the response.
     *
     * @param Request $request request containing a `task_ids` array of task identifiers to retrieve
     */
    public function processTaskSelections(Request $request): void
    {
        $taskIds = $request->input('task_ids', []);
        $tasks   = (new TasksService())->query()->whereIn('task_id', $taskIds)->get();
        foreach ($tasks as $task) {
            $task->task_price = format_amount($task->task_price);
        }
        echo json_encode($tasks);
    }
}
