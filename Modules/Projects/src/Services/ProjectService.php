<?php

namespace Modules\Projects\Services;

use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Project;

/**
 * ProjectService.
 *
 * Service class for managing project business logic
 */
class ProjectService extends BaseService
{
    /**
     * Get all projects ordered by name.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllOrdered(): \Illuminate\Database\Eloquent\Collection
    {
        return Project::query()->orderBy('project_name')->get();
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/projects/models/Mdl_project.php
     *
     * @legacy-function get_latest()
     */
    public function getLatest()
    {
/*
        $this->db->order_by('ip_projects.project_id', 'DESC');

        return $this;
*/
    }


    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/projects/models/Mdl_project.php
     *
     * @legacy-function get_tasks()
     */
    public function getTasks($project_id)
    {
/*
        $result = [];

        if ( ! $project_id) {
            return $result;
        }

        $this->load->model('tasks/task');
        $query = $this->mdl_tasks->where('ip_tasks.project_id', $project_id)->get();

        foreach ($query->result() as $row) {
            $result[] = $row;
        }

        return $result;
*/
    }

    /**
     * Check if project can be deleted.
     *
     * @param int $id Project ID
     *
     * @return bool True if project can be deleted
     */
    public function canDelete(int $id): bool
    {
        $blockers = $this->getDeletionBlockers($id);

        return $blockers['tasks'] === 0;
    }

    /**
     * Get deletion blockers for project.
     *
     * @param int $id Project ID
     *
     * @return array Array of blocker counts
     */
    public function getDeletionBlockers(int $id): array
    {
        return [
            'tasks' => \Modules\Projects\Models\Task::query()
                ->where('project_id', $id)
                ->count(),
        ];
    }

    protected function getModelClass(): string
    {
        return Project::class;
    }
}
