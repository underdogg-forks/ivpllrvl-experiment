<?php

namespace Tests\Unit\Services\Projects;

use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\TaskService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TaskService Unit Tests.
 *
 * Test suite for TaskService business logic methods.
 */
#[CoversClass(TaskService::class)]
class TaskServiceTest extends TestCase
{
    private TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskService();
    }

    /**
     * Test that service returns correct model class.
     */
    #[Test]
    public function it_returns_correct_model_class(): void
    {
        /** Arrange & Act */
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getModelClass');
        $method->setAccessible(true);
        $modelClass = $method->invoke($this->service);

        /** Assert */
        $this->assertEquals(Task::class, $modelClass);
    }

    /**
     * Test that create method creates a new task.
     */
    #[Test]
    public function it_creates_task(): void
    {
        /** Arrange */
        $project = Project::factory()->create();
        $data = [
            'project_id' => $project->project_id,
            'task_name'  => 'Test Task',
            'task_status' => 1,
        ];

        /** Act */
        $task = $this->service->create($data);

        /** Assert */
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Task', $task->task_name);
        $this->assertEquals($project->project_id, $task->project_id);
        $this->assertDatabaseHas('ip_tasks', [
            'task_name' => 'Test Task',
        ]);
    }

    /**
     * Test that create method creates task without project.
     */
    #[Test]
    public function it_creates_task_without_project(): void
    {
        /** Arrange */
        $data = [
            'task_name'   => 'Standalone Task',
            'task_status' => 1,
        ];

        /** Act */
        $task = $this->service->create($data);

        /** Assert */
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Standalone Task', $task->task_name);
        $this->assertNull($task->project_id);
    }

    /**
     * Test that update method updates existing task.
     */
    #[Test]
    public function it_updates_task(): void
    {
        /** Arrange */
        $task = Task::factory()->create([
            'task_name' => 'Old Name',
        ]);

        $updateData = [
            'task_name' => 'Updated Name',
        ];

        /** Act */
        $result = $this->service->update($task->task_id, $updateData);

        /** Assert */
        $this->assertTrue($result);
        $this->assertDatabaseHas('ip_tasks', [
            'task_id'   => $task->task_id,
            'task_name' => 'Updated Name',
        ]);
    }

    /**
     * Test that find method returns task.
     */
    #[Test]
    public function it_finds_task_by_id(): void
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $found = $this->service->find($task->task_id);

        /** Assert */
        $this->assertInstanceOf(Task::class, $found);
        $this->assertEquals($task->task_id, $found->task_id);
    }

    /**
     * Test that findOrFail throws exception for non-existent task.
     */
    #[Test]
    public function it_throws_exception_when_task_not_found(): void
    {
        /** Arrange */
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        /** Act */
        $this->service->findOrFail(999999);
    }

    /**
     * Test that delete method deletes task.
     */
    #[Test]
    public function it_deletes_task(): void
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $result = $this->service->delete($task->task_id);

        /** Assert */
        $this->assertTrue($result);
        $this->assertDatabaseMissing('ip_tasks', [
            'task_id' => $task->task_id,
        ]);
    }
}
