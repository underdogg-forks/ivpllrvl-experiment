<?php

namespace Modules\Projects\Controllers;

use Modules\Projects\Http\Requests\TaskRequest;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\ProjectService;
use Modules\Projects\Services\TaskService;
use Modules\Products\Models\TaxRate;
use Modules\Products\Services\TaxRateService;

use Modules\Core\Support\TranslationHelper;
class TasksController
{    public function __construct(
        protected TaskService $taskService,
        protected ProjectService $projectService,
        protected TaxRateService $taxRateService
    ) {
    }

    public function index(int $page = 0): \Illuminate\View\View
    {
        $tasks = $this->taskService->getAllWithRelations(['project', 'taxRate'], 15);

        return view('projects::tasks_index', [
            'filter_display'     => true,
            'filter_placeholder' => TranslationHelper::trans('filter_tasks'),
            'filter_method'      => 'filter_tasks',
            'tasks'              => $tasks,
            'task_statuses'      => Task::STATUSES,
        ]);
    }

    public function form(?int $id = null): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // Handle POST request (create/update)
        if (request()->isMethod('post')) {
            $request = app(TaskRequest::class);
            
            if ($id) {
                // Update existing task
                $this->taskService->update($id, $request->validated());
            } else {
                // Create new task
                $this->taskService->create($request->validated());
            }
            
            return redirect()->route('tasks.index')
                ->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }
        
        // Handle GET request (show form)
        $task = $id ? Task::findOrFail($id) : new Task();
        $projects = $this->projectService->getAllOrdered();
        $taxRates = $this->taxRateService->getAllOrdered();

        return view('projects::tasks_form', [
            'task'          => $task,
            'projects'      => $projects,
            'task_statuses' => Task::STATUSES,
            'tax_rates'     => $taxRates,
        ]);
    }

    public function destroy(Task $task): \Illuminate\Http\RedirectResponse
    {
        $this->taskService->delete($task->task_id);
        return redirect()->route('tasks.index')->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}
