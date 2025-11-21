<?php

namespace Modules\Projects\Controllers;

use Modules\Core\Support\TranslationHelper;
use Modules\Core\Traits\HandlesDeletion;
use Modules\Products\Services\TaxRateService;
use Modules\Projects\Http\Requests\TaskRequest;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\ProjectService;
use Modules\Projects\Services\TaskService;

class TasksController
{
    use HandlesDeletion;

    public function __construct(
        protected TaskService $taskService,
        protected ProjectService $projectService,
        protected TaxRateService $taxRateService
    ) {}

    /**
     * @param int $page
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     *
     * @legacy-function index()
     */
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

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     *
     * @legacy-function form()
     */
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
        $task     = $id ? Task::findOrFail($id) : new Task();
        $projects = $this->projectService->getAllOrdered();
        $taxRates = $this->taxRateService->getAllOrdered();

        return view('projects::tasks_form', [
            'task'          => $task,
            'projects'      => $projects,
            'task_statuses' => Task::STATUSES,
            'tax_rates'     => $taxRates,
        ]);
    }

    /**
     * Delete a task with security checks and error handling.
     *
     * Implements:
     * - Early returns for validation
     * - DRY principle via HandlesDeletion trait
     * - Business rule: Cannot delete tasks assigned to invoices
     *
     * @param int $id Task ID
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     *
     * @legacy-function delete()
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        // Validate ID
        if ($id <= 0) {
            return $this->redirectWithError('tasks.index', TranslationHelper::trans('invalid_task_id'));
        }

        // Check if task exists
        $task = $this->taskService->find($id);
        if ( ! $task) {
            return $this->redirectWithError('tasks.index', TranslationHelper::trans('task_not_found'));
        }

        // Business rule: Cannot delete tasks that are assigned to invoices
        if ( ! $this->taskService->canDelete($id)) {
            return $this->redirectWithError(
                'tasks.index',
                TranslationHelper::trans('task_deletion_not_allowed_assigned_to_invoice')
            );
        }

        // Execute delete with standardized error handling
        return $this->executeDelete(
            fn () => $this->taskService->delete($id),
            'tasks.index'
        );
    }

    /**
     * @param $id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/tasks/controllers/Tasks.php
     *
     * @legacy-function delete()
     */
    public function destroy(Task $task): \Illuminate\Http\RedirectResponse
    {
        $this->taskService->delete($task->task_id);

        return redirect()->route('tasks.index')->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}
