<?php

namespace Modules\Projects\Controllers;

use AllowDynamicProperties;
use Modules\Core\Controllers\AdminController;
use Modules\Projects\app\Services\TasksService;
use Modules\Projects\Services\ProjectsService;

#[AllowDynamicProperties]
class ProjectsController extends AdminController
{
    /**
     * Initialize the ProjectsController and perform the parent controller setup.
     */
    public function __construct(
        private ProjectsService $projectsService,
        private TasksService $tasksService
    ) {
        parent::__construct();
    }

    /**
     * @originalName index
     *
     * @param int $page the page number to display (zero-based)
     *
     * @return string rendered view for the projects index populated with projects and filter settings
     */
    public function index($page = 0)
    {
        $this->projectsService->paginate(site_url('projects/index'), $page);
        $projects = $this->projectsService->result();

        return view('projects.index', ['filter_display' => true, 'filter_placeholder' => trans('filter_projects'), 'filter_method' => 'filter_projects', 'projects' => $projects]);
    }

    /**
     * Display and process the project creation/edit form.
     *
     * Processes form submission, validates and saves project data, and renders the project form populated with the project and active clients when not redirected.
     *
     * @param int|null $id the project identifier to edit, or null to create a new project
     *
     * @return string The rendered HTML of the project form view.
     *
     * Note: this method may redirect to the projects list on cancel or after a successful save, and will trigger a 404 response if the provided `$id` cannot be prepared for editing.
     */
    public function form($id = null)
    {
        if (request()->input('btn_cancel')) {
            redirect()->route('projects');
        }
        $this->filterInput();
        // <<<--- filters _POST array for nastiness
        if ($this->projectsService->runValidation()) {
            $this->projectsService->save($id);
            redirect()->route('projects');
        }
        if ($id && ! request()->input('btn_submit') && ! (new ProjectsService())->prepForm($id)) {
            show_404();
        }
    }

    /**
     * Show a project's details with its tasks and available task statuses.
     *
     * Triggers a 404 response if the specified project does not exist. If the cancel button is submitted, redirects to the projects list.
     *
     * @param int|string $project_id identifier of the project to display
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface|string the rendered project view response or a redirect response when cancelled
     */
    public function view($project_id)
    {
        if (request()->input('btn_cancel')) {
            redirect()->route('projects');
        }
        $project = $this->projectsService->getById($project_id);
        if ( ! $project) {
            show_404();
        }

        return view('projects.view', ['project' => $project, 'tasks' => (new ProjectsService())->getTasks($project->project_id), 'task_statuses' => (new TasksService())->statuses()]);
    }

    /**
     * Delete the specified project and disassociate it from related tasks, then redirect to the projects list.
     *
     * Removes the project identified by `$id`, updates any tasks that referenced the project so they no longer do, and redirects the user to the projects index route.
     *
     * @param int|string $id the ID of the project to delete
     */
    public function delete($id)
    {
        $this->tasksService->updateOnProjectDelete($id);
        $this->projectsService->delete($id);
        redirect()->route('projects');
    }
}
