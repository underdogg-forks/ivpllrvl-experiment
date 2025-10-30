<?php

namespace Modules\Crm\Controllers;

use Modules\Crm\Models\Task;
use Modules\Crm\Models\Project;

class TasksController
{
    /** @legacy-file application/modules/tasks/controllers/Tasks.php:32 */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $tasks = Task::with(['project', 'taxRate'])->orderBy('task_name')->paginate(15, ['*'], 'page', $page);
        return view('crm::tasks_index', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_tasks'),
            'filter_method' => 'filter_tasks',
            'tasks' => $tasks,
            'task_statuses' => Task::STATUSES,
        ]);
    }

    /** @legacy-file application/modules/tasks/controllers/Tasks.php:50 */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) return redirect()->route('tasks.index');

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate(Task::validationRules());
            if ($id) {
                Task::query()->findOrFail($id)->update($validated);
            } else {
                Task::query()->create($validated);
            }
            return redirect()->route('tasks.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $task = $id ? Task::query()->findOrFail($id) : new Task();
        $projects = Project::query()->orderBy('project_name')->get();
        $taxRates = \Modules\Products\Models\TaxRate::query()->orderBy('tax_rate_name')->get();

        return view('crm::tasks_form', [
            'task' => $task,
            'projects' => $projects,
            'task_statuses' => Task::STATUSES,
            'tax_rates' => $taxRates,
        ]);
    }

    /** @legacy-file application/modules/tasks/controllers/Tasks.php:87 */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        Task::query()->findOrFail($id)->delete();
        return redirect()->route('tasks.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
