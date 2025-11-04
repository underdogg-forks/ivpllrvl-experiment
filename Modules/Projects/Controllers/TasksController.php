<?php

namespace Modules\Projects\Controllers;

use Modules\Projects\Http\Requests\TaskRequest;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\TaskService;
use Modules\Products\Models\TaxRate;

use Modules\Core\Support\TranslationHelper;
class TasksController
{    public function __construct(
        protected TaskService $taskService
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

    public function create(): \Illuminate\View\View
    {
        $task     = new Task();
        $projects = Project::query()->orderBy('project_name')->get();
        $taxRates = TaxRate::query()->orderBy('tax_rate_name')->get();

        return view('projects::tasks_form', [
            'task'          => $task,
            'projects'      => $projects,
            'task_statuses' => Task::STATUSES,
            'tax_rates'     => $taxRates,
        ]);
    }

    public function store(TaskRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->taskService->create($request->validated());
        return redirect()->route('tasks.index')->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
    }

    public function edit(Task $task): \Illuminate\View\View
    {
        $projects = Project::query()->orderBy('project_name')->get();
        $taxRates = TaxRate::query()->orderBy('tax_rate_name')->get();

        return view('projects::tasks_form', [
            'task'          => $task,
            'projects'      => $projects,
            'task_statuses' => Task::STATUSES,
            'tax_rates'     => $taxRates,
        ]);
    }

    public function update(TaskRequest $request, Task $task): \Illuminate\Http\RedirectResponse
    {
        $this->taskService->update($task->task_id, $request->validated());
        return redirect()->route('tasks.index')->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
    }

    public function destroy(Task $task): \Illuminate\Http\RedirectResponse
    {
        $this->taskService->delete($task->task_id);
        return redirect()->route('tasks.index')->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}
