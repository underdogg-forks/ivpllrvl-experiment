<?php

namespace Modules\Projects\Controllers;

use Modules\Projects\Http\Requests\ProjectRequest;
use Modules\Crm\Models\Client;
use Modules\Projects\Models\Project;
use Modules\Projects\Services\ProjectService;

class ProjectsController
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(int $page = 0): \Illuminate\View\View
    {
        $projects = Project::with('client')->orderBy('project_name')->paginate(15, ['*'], 'page', $page);

        return view('projects::projects_index', [
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_projects'),
            'filter_method'      => 'filter_projects',
            'projects'           => $projects,
        ]);
    }

    public function create(): \Illuminate\View\View
    {
        $project = new Project();
        $clients = Client::where('client_active', 1)->orderBy('client_name')->get();

        return view('projects::projects_form', ['project' => $project, 'clients' => $clients]);
    }

    public function store(ProjectRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->projectService->create($request->validated());
        return redirect()->route('projects.index')->with('alert_success', trans('record_successfully_saved'));
    }

    public function edit(Project $project): \Illuminate\View\View
    {
        $clients = Client::where('client_active', 1)->orderBy('client_name')->get();
        return view('projects::projects_form', ['project' => $project, 'clients' => $clients]);
    }

    public function update(ProjectRequest $request, Project $project): \Illuminate\Http\RedirectResponse
    {
        $this->projectService->update($project->project_id, $request->validated());
        return redirect()->route('projects.index')->with('alert_success', trans('record_successfully_saved'));
    }

    public function view(Project $project): \Illuminate\View\View
    {
        $project->load(['client', 'tasks']);

        return view('projects::projects_view', [
            'project' => $project,
            'tasks'   => $project->tasks,
        ]);
    }

    public function destroy(Project $project): \Illuminate\Http\RedirectResponse
    {
        $this->projectService->delete($project->project_id);
        return redirect()->route('projects.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
