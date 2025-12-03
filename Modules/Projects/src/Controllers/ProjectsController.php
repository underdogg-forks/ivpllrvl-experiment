<?php

namespace Modules\Projects\Controllers;

use Modules\Core\Support\TranslationHelper;
use Modules\Core\Traits\HandlesDeletion;
use Modules\Projects\Models\Project;
use Modules\Projects\Services\ProjectService;
use Modules\Projects\Services\TaskService;

/**
 * ProjectsController.
 *
 * Manages project CRUD operations and project task relationships
 * Implements SOLID principles, DRY pattern, and early returns
 *
 * @legacy-file application/modules/projects/controllers/Projects.php
 */
class ProjectsController
{
    use HandlesDeletion;

    public function __construct(
        protected ProjectService $projectService,
        protected TaskService $taskService
    ) {}

    /**
     * Display a paginated list of projects.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-file application/modules/projects/controllers/Projects.php
     *
     * @legacy-function index
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
     * @legacy-file application/modules/projects/controllers/Projects.php
     *
     * @legacy-function form
     */
    public function form(?int $id = null)
    {
        // Early return for cancel action
        if (request()->has('btn_cancel')) {
            return redirect()->route('projects.index');
        }

        // Early return for form submission
        if (request()->isMethod('post') && request()->has('btn_submit')) {
            return $this->handleFormSubmission($id);
        }

        return $this->showForm($id);
    }

    /**
     * Display a specific project with its tasks.
     *
     * @param int $projectId Project ID
     *
     * @return \Illuminate\View\View
     *
     * @legacy-file application/modules/projects/controllers/Projects.php
     *
     * @legacy-function view
     */
    public function view(): \Illuminate\View\View
    {
        $projectId = request()->get('id');

        $project = $this->projectService->find($projectId);

        // Early return with 404 if not found
        if ( ! $project) {
            abort(404);
        }

        $tasks = $this->projectService->getTasks($projectId);

        return view('projects::projects_view', [
            'project'       => $project,
            'tasks'         => $tasks,
            'task_statuses' => $this->taskService->getStatuses(),
        ]);
    }

    /**
     * Delete a project with security checks and error handling.
     *
     * Implements:
     * - Early returns for validation
     * - DRY principle via HandlesDeletion trait
     * - SOLID principles with single responsibility
     * - Proper error handling
     *
     * @param int $id Project ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-file application/modules/projects/controllers/Projects.php
     *
     * @legacy-function delete
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        // Early return for validation
        if ($id <= 0) {
            return $this->redirectWithError('projects.index', TranslationHelper::trans('invalid_project_id'));
        }

        // Check if project exists
        $project = $this->projectService->find($id);
        if ( ! $project) {
            return $this->redirectWithError('projects.index', TranslationHelper::trans('project_not_found'));
        }

        // Business rule: Cannot delete projects with related tasks
        if ( ! $this->projectService->canDelete($id)) {
            $blockers = $this->projectService->getDeletionBlockers($id);
            $message  = TranslationHelper::trans('project_deletion_not_allowed', [
                'tasks' => $blockers['tasks'] ?? 0,
            ]);

            return $this->redirectWithError('projects.index', $message);
        }

        // Execute delete with standardized error handling
        return $this->executeDelete(
            fn () => $this->projectService->delete($id),
            'projects.index'
        );
    }

    /**
     * Handle form submission for create/update.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function handleFormSubmission(?int $id): \Illuminate\Http\RedirectResponse
    {
        $validated = request()->validate([
            'project_name'        => 'required|string|max:255',
            'client_id'           => 'nullable|integer|exists:ip_clients,client_id',
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

    /**
     * Show the form for create/edit.
     *
     * @param int|null $id
     *
     * @return \Illuminate\View\View
     */
    protected function showForm(?int $id): \Illuminate\View\View
    {
        $project = $id ? $this->projectService->find($id) : new Project();

        // Early return with 404 if project not found
        if ($id && ! $project) {
            abort(404);
        }

        return view('projects::projects_form', ['project' => $project]);
    }
}
