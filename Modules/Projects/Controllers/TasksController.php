<?php

declare(strict_types=1);

namespace Modules\Projects\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Products\Models\TaxRate;
use Modules\Projects\Http\Requests\TaskRequest;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\TaskService;

/**
 * TasksController
 *
 * Handles task management operations including listing, creating, viewing,
 * updating, and deleting tasks. Tasks can be assigned to projects and have
 * tax rates associated with them.
 *
 * @legacy-file application/modules/tasks/controllers/Tasks.php
 */
class TasksController
{
    /**
     * @param TaskService $taskService Service for task business logic
     */
    public function __construct(
        private readonly TaskService $taskService
    ) {
    }

    /**
     * Display a paginated list of tasks with their associated projects and tax rates.
     *
     * @param int $page Page number for pagination (default: 0)
     *
     * @return View
     *
     * @legacy-function index
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     */
    public function index(int $page = 0): View
    {
        $tasks = Task::query()
            ->with(['project', 'taxRate'])
            ->orderBy('task_name')
            ->paginate(15, ['*'], 'page', $page);

        return view('projects::tasks_index', [
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_tasks'),
            'filter_method'      => 'filter_tasks',
            'tasks'              => $tasks,
            'task_statuses'      => Task::STATUSES,
        ]);
    }

    /**
     * Show the form for creating a new task.
     *
     * Provides empty task instance along with available projects and tax rates
     * for selection in the form.
     *
     * @return View
     *
     * @legacy-function form (new task)
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     */
    public function create(): View
    {
        $task = new Task();
        $projects = Project::query()->orderBy('project_name')->get();
        $taxRates = TaxRate::query()->orderBy('tax_rate_name')->get();

        return view('projects::tasks_form', [
            'task'          => $task,
            'projects'      => $projects,
            'task_statuses' => Task::STATUSES,
            'tax_rates'     => $taxRates,
        ]);
    }

    /**
     * Store a newly created task in the database.
     *
     * @param TaskRequest $request Validated request data
     *
     * @return RedirectResponse
     *
     * @legacy-function form (save)
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     */
    public function store(TaskRequest $request): RedirectResponse
    {
        $this->taskService->create($request->validated());
        
        return redirect()
            ->route('tasks.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Show the form for editing the specified task.
     *
     * @param Task $task The task to edit (route model binding)
     *
     * @return View
     *
     * @legacy-function form (edit task)
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     */
    public function edit(Task $task): View
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

    /**
     * Update the specified task in the database.
     *
     * @param TaskRequest $request Validated request data
     * @param Task        $task    The task to update (route model binding)
     *
     * @return RedirectResponse
     *
     * @legacy-function form (update)
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $this->taskService->update($task->task_id, $request->validated());
        
        return redirect()
            ->route('tasks.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Remove the specified task from the database.
     *
     * @param Task $task The task to delete (route model binding)
     *
     * @return RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->taskService->delete($task->task_id);
        
        return redirect()
            ->route('tasks.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
