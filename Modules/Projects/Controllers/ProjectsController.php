<?php

namespace Modules\Projects\Controllers;

use Modules\Projects\Models\Project;
use Modules\Projects\Services\ProjectService;
use Modules\Projects\Services\TaskService;

use Modules\Core\Support\TranslationHelper;
/**
 * ProjectsController
 *
 * Manages project CRUD operations and project task relationships
 *
 * @legacy-file application/modules/projects/controllers/Projects.php
 */
class ProjectsController
{
    public function __construct(
        protected ProjectService $projectService,
        protected TaskService $taskService
    ) {
    }

    /**
     * Display a paginated list of projects.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/projects/controllers/Projects.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $projects = Project::query()
            ->orderBy('project_name')
            ->paginate(15, ['*'], 'page', $page);

        return view('projects::projects_index', [
            'filter_display'     => true,
            'filter_placeholder' => TranslationHelper::trans('filter_projects'),
            'filter_method'      => 'filter_projects',
            'projects'           => $projects,
        ]);
    }

    /**
     * Display form for creating or editing a project.
     *
     * @param int|null $id Project ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     * @legacy-file application/modules/projects/controllers/Projects.php
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('projects.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'project_name' => 'required|string|max:255',
                'client_id' => 'nullable|integer|exists:ip_clients,client_id',
                'project_description' => 'nullable|string',
            ]);

            if ($id) {
                $this->projectService->update($id, $validated);
            } else {
                $this->projectService->create($validated);
            }

            return redirect()->route('projects.index')
                ->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }

        $project = $id ? $this->projectService->find($id) : new Project();
        if ($id && !$project) {
            abort(404);
        }

        return view('projects::projects_form', ['project' => $project]);
    }

    /**
     * Display a specific project with its tasks.
     *
     * @param int $projectId Project ID
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function view
     * @legacy-file application/modules/projects/controllers/Projects.php
     */
    public function view(int $projectId): \Illuminate\View\View
    {
        $project = $this->projectService->find($projectId);
        if (!$project) {
            abort(404);
        }

        $tasks = $this->projectService->getTasks($projectId);

        return view('projects::projects_view', [
            'project' => $project,
            'tasks' => $tasks,
            'task_statuses' => $this->taskService->getStatuses(),
        ]);
    }

    /**
     * Delete a project.
     *
     * @param int $id Project ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/projects/controllers/Projects.php
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->taskService->updateOnProjectDelete($id);
        $this->projectService->delete($id);

        return redirect()->route('projects.index')
            ->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}
