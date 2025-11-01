<?php

namespace Modules\Projects\Tests\Feature;

use Modules\Crm\Models\Client;
use Modules\Projects\Controllers\ProjectsController;
use Modules\Projects\Models\Project;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * ProjectsController Feature Tests.
 *
 * Test suite for ProjectsController covering CRUD operations
 * with data integrity validation and business logic verification.
 */
#[CoversClass(ProjectsController::class)]
class ProjectsControllerTest extends FeatureTestCase
{
    /**
     * Test that index method displays list of projects.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_list_of_projects(): void
    {
        /** Arrange */
        $client = Client::factory()->create();
        $project = Project::factory()->create([
            'client_id' => $client->client_id,
        ]);

        /** Act */
        $response = $this->get(route('projects.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('projects::projects_index');
        $response->assertViewHas('projects');

        /** Verify project is in the list */
        $projects = $response->viewData('projects');
        $projectIds = $projects->pluck('project_id')->toArray();
        $this->assertContains($project->project_id, $projectIds);
    }

    /**
     * Test that create method displays project form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_project_create_form(): void
    {
        /** Arrange */
        $client = Client::factory()->create(['client_active' => 1]);

        /** Act */
        $response = $this->get(route('projects.create'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('projects::projects_form');
        $response->assertViewHas('project');
        $response->assertViewHas('clients');

        /** Verify new project instance is passed */
        $project = $response->viewData('project');
        $this->assertInstanceOf(Project::class, $project);
        $this->assertFalse($project->exists);
    }

    /**
     * Test that store method creates new project with valid data.
     */
    #[Group('crud')]
    #[Test]
    public function it_creates_new_project_with_valid_data(): void
    {
        /** Arrange */
        $client = Client::factory()->create();
        /**
         * {
         *     "client_id": 1,
         *     "project_name": "Test Project",
         *     "project_status": 1
         * }
         */
        $projectData = [
            'client_id'      => $client->client_id,
            'project_name'   => 'Test Project',
            'project_status' => 1,
        ];

        /** Act */
        $response = $this->post(route('projects.store'), $projectData);

        /** Assert */
        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('alert_success');

        /** Verify project was created in database */
        $this->assertDatabaseHas('ip_projects', [
            'client_id'    => $client->client_id,
            'project_name' => 'Test Project',
        ]);
    }

    /**
     * Test that store method fails with invalid data.
     */
    #[Test]
    public function it_fails_to_create_project_with_invalid_data(): void
    {
        /** Arrange */
        /**
         * {
         *     "project_name": "Test Project"
         * }
         */
        $projectData = [
            'project_name' => 'Test Project',
            // Missing required client_id
        ];

        /** Act */
        $response = $this->post(route('projects.store'), $projectData);

        /** Assert */
        $response->assertSessionHasErrors(['client_id']);
    }

    /**
     * Test that edit method displays project edit form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_project_edit_form(): void
    {
        /** Arrange */
        $client = Client::factory()->create(['client_active' => 1]);
        $project = Project::factory()->create([
            'client_id' => $client->client_id,
        ]);

        /** Act */
        $response = $this->get(route('projects.edit', ['project' => $project->project_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('projects::projects_form');
        $response->assertViewHas('project');
        $response->assertViewHas('clients');

        /** Verify correct project is passed */
        $viewProject = $response->viewData('project');
        $this->assertEquals($project->project_id, $viewProject->project_id);
    }

    /**
     * Test that update method updates existing project.
     */
    #[Group('crud')]
    #[Test]
    public function it_updates_existing_project_with_valid_data(): void
    {
        /** Arrange */
        $client = Client::factory()->create();
        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => 'Old Name',
        ]);

        /**
         * {
         *     "client_id": 1,
         *     "project_name": "Updated Name"
         * }
         */
        $updateData = [
            'client_id'    => $client->client_id,
            'project_name' => 'Updated Name',
        ];

        /** Act */
        $response = $this->put(route('projects.update', ['project' => $project->project_id]), $updateData);

        /** Assert */
        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('alert_success');

        /** Verify project was updated */
        $this->assertDatabaseHas('ip_projects', [
            'project_id'   => $project->project_id,
            'project_name' => 'Updated Name',
        ]);
    }

    /**
     * Test that view method displays project details with related data.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_project_view_with_related_data(): void
    {
        /** Arrange */
        $client = Client::factory()->create();
        $project = Project::factory()->create([
            'client_id' => $client->client_id,
        ]);

        /** Act */
        $response = $this->get(route('projects.view', ['project' => $project->project_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('projects::projects_view');
        $response->assertViewHas('project');
        $response->assertViewHas('tasks');

        /** Verify correct project is passed */
        $viewProject = $response->viewData('project');
        $this->assertEquals($project->project_id, $viewProject->project_id);
    }

    /**
     * Test that destroy method deletes project.
     */
    #[Group('crud')]
    #[Test]
    public function it_deletes_project(): void
    {
        /** Arrange */
        $client = Client::factory()->create();
        $project = Project::factory()->create([
            'client_id' => $client->client_id,
        ]);

        /** Act */
        $response = $this->delete(route('projects.destroy', ['project' => $project->project_id]));

        /** Assert */
        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('alert_success');

        /** Verify project was deleted */
        $this->assertDatabaseMissing('ip_projects', [
            'project_id' => $project->project_id,
        ]);
    }
}
