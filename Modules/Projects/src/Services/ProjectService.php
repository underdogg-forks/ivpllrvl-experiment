<?php

namespace Modules\Projects\Services;

use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

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
     * Get latest projects (ordered by descending ID).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/projects/models/Mdl_project.php
     *
     * @legacy-function get_latest()
     */
    public function getLatest()
    {
        return Project::query()->orderByDesc('project_id');
    }

    /**
     * Get tasks for a specific project.
     *
     * @param int $projectId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/projects/models/Mdl_project.php
     *
     * @legacy-function get_tasks()
     */
    public function getTasks(int $projectId)
    {
        if ( ! $projectId) {
            return collect();
        }

        return Task::query()
            ->where('project_id', $projectId)
            ->get();
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
            'tasks' => Task::query()
                ->where('project_id', $id)
                ->count(),
        ];
    }

    /**
     * Get the model class managed by this service.
     */
    protected function getModelClass(): string
    {
        return Project::class;
    }
}
