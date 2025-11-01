<?php

namespace Modules\Projects\Tests\Feature;

use Modules\Projects\Controllers\TasksController;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * TasksController Feature Tests.
 *
 * Test suite for TasksController covering CRUD operations
 * with data integrity validation and business logic verification.
 */
#[CoversClass(TasksController::class)]
class TasksControllerTest extends FeatureTestCase
{
    /**
     * Test that index method displays list of tasks.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_list_of_tasks(): void
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $response = $this->get(route('tasks.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('projects::tasks_index');
        $response->assertViewHas('tasks');
        $response->assertViewHas('task_statuses');

        /** Verify task is in the list */
        $tasks = $response->viewData('tasks');
        $taskIds = $tasks->pluck('task_id')->toArray();
        $this->assertContains($task->task_id, $taskIds);
    }

    /**
     * Test that create method displays task form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_task_create_form(): void
    {
        /** Act */
        $response = $this->get(route('tasks.create'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('projects::tasks_form');
        $response->assertViewHas('task');
        $response->assertViewHas('projects');
        $response->assertViewHas('task_statuses');

        /** Verify new task instance is passed */
        $task = $response->viewData('task');
        $this->assertInstanceOf(Task::class, $task);
        $this->assertFalse($task->exists);
    }

    /**
     * Test that store method creates new task with valid data.
     */
    #[Group('crud')]
    #[Test]
    public function it_creates_new_task_with_valid_data(): void
    {
        /** Arrange */
        $project = Project::factory()->create();
        /**
         * {
         *     "project_id": 1,
         *     "task_name": "Test Task",
         *     "task_status": 1,
         *     "task_finish_date": "2025-12-31"
         * }
         */
        $taskData = [
            'project_id'       => $project->project_id,
            'task_name'        => 'Test Task',
            'task_status'      => 1,
            'task_finish_date' => '2025-12-31',
        ];

        /** Act */
        $response = $this->post(route('tasks.store'), $taskData);

        /** Assert */
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('alert_success');

        /** Verify task was created in database */
        $this->assertDatabaseHas('ip_tasks', [
            'project_id' => $project->project_id,
            'task_name'  => 'Test Task',
        ]);
    }

    /**
     * Test that store method fails with invalid data.
     */
    #[Test]
    public function it_fails_to_create_task_with_invalid_data(): void
    {
        /** Arrange */
        /**
         * {
         *     "project_id": 999
         * }
         */
        $taskData = [
            'project_id' => 999,
            // Missing required task_name
        ];

        /** Act */
        $response = $this->post(route('tasks.store'), $taskData);

        /** Assert */
        $response->assertSessionHasErrors(['task_name']);
    }

    /**
     * Test that edit method displays task edit form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_task_edit_form(): void
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $response = $this->get(route('tasks.edit', ['task' => $task->task_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('projects::tasks_form');
        $response->assertViewHas('task');
        $response->assertViewHas('projects');
        $response->assertViewHas('task_statuses');

        /** Verify correct task is passed */
        $viewTask = $response->viewData('task');
        $this->assertEquals($task->task_id, $viewTask->task_id);
    }

    /**
     * Test that update method updates existing task.
     */
    #[Group('crud')]
    #[Test]
    public function it_updates_existing_task_with_valid_data(): void
    {
        /** Arrange */
        $task = Task::factory()->create([
            'task_name' => 'Old Name',
        ]);

        /**
         * {
         *     "task_name": "Updated Name",
         *     "task_status": 2
         * }
         */
        $updateData = [
            'task_name'   => 'Updated Name',
            'task_status' => 2,
        ];

        /** Act */
        $response = $this->put(route('tasks.update', ['task' => $task->task_id]), $updateData);

        /** Assert */
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('alert_success');

        /** Verify task was updated */
        $this->assertDatabaseHas('ip_tasks', [
            'task_id'   => $task->task_id,
            'task_name' => 'Updated Name',
        ]);
    }

    /**
     * Test that destroy method deletes task.
     */
    #[Group('crud')]
    #[Test]
    public function it_deletes_task(): void
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $response = $this->delete(route('tasks.destroy', ['task' => $task->task_id]));

        /** Assert */
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('alert_success');

        /** Verify task was deleted */
        $this->assertDatabaseMissing('ip_tasks', [
            'task_id' => $task->task_id,
        ]);
    }

    /**
     * Test that tasks can be created without a project.
     */
    #[Group('crud')]
    #[Test]
    public function it_creates_task_without_project(): void
    {
        /** Arrange */
        /**
         * {
         *     "task_name": "Standalone Task",
         *     "task_status": 1
         * }
         */
        $taskData = [
            'task_name'   => 'Standalone Task',
            'task_status' => 1,
        ];

        /** Act */
        $response = $this->post(route('tasks.store'), $taskData);

        /** Assert */
        $response->assertRedirect(route('tasks.index'));

        /** Verify task was created without project */
        $this->assertDatabaseHas('ip_tasks', [
            'task_name'  => 'Standalone Task',
            'project_id' => null,
        ]);
    }
}
