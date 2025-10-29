<?php

namespace Modules\Crm\Controllers;

use Modules\Crm\Models\Project;
use Modules\Crm\Models\Client;

class ProjectsController
{
    /** @legacy-file application/modules/projects/controllers/Projects.php:32 */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $projects = Project::with('client')->orderBy('project_name')->paginate(15, ['*'], 'page', $page);
        return view('crm::projects_index', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_projects'),
            'filter_method' => 'filter_projects',
            'projects' => $projects,
        ]);
    }

    /** @legacy-file application/modules/projects/controllers/Projects.php:49 */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) return redirect()->route('projects.index');
        
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate(Project::validationRules());
            if ($id) {
                Project::query()->findOrFail($id)->update($validated);
            } else {
                Project::query()->create($validated);
            }
            return redirect()->route('projects.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $project = $id ? Project::query()->find($id) : new Project();
        if ($id && !$project) abort(404);
        
        $clients = Client::query()->where('client_active', 1)->orderBy('client_name')->get();
        return view('crm::projects_form', ['project' => $project, 'clients' => $clients]);
    }

    /** @legacy-file application/modules/projects/controllers/Projects.php:80 */
    public function view(int $projectId): \Illuminate\View\View
    {
        $project = Project::with(['client', 'tasks'])->findOrFail($projectId);
        return view('crm::projects_view', [
            'project' => $project,
            'tasks' => $project->tasks,
        ]);
    }

    /** @legacy-file application/modules/projects/controllers/Projects.php:106 */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $project = Project::query()->findOrFail($id);
        $project->delete();
        return redirect()->route('projects.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
