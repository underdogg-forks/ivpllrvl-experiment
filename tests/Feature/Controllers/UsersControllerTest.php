<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\UsersController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * UsersController Feature Tests.
 *
 * Tests user account management including CRUD operations.
 */
#[CoversClass(UsersController::class)]
class UsersControllerTest extends FeatureTestCase
{
    /**
     * Test index displays paginated list of users.
     */
    #[Test]
    public function it_displays_paginated_list_of_users(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        User::factory()->count(5)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('users.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('users::index');
        $response->assertViewHas('users');
        $response->assertViewHas('filter_display', true);
        $response->assertViewHas('filter_placeholder');
        $response->assertViewHas('filter_method', 'filter_users');
        $response->assertViewHas('user_types');
    }

    /**
     * Test users are ordered alphabetically by name.
     */
    #[Test]
    public function it_orders_users_alphabetically_by_name(): void
    {
        /** Arrange */
        $adminUser = User::factory()->create(['user_name' => 'Admin']);
        
        User::factory()->create(['user_name' => 'Zack']);
        User::factory()->create(['user_name' => 'Alice']);
        User::factory()->create(['user_name' => 'Bob']);

        /** Act */
        $response = $this->actingAs($adminUser)->get(route('users.index'));

        /** Assert */
        $response->assertOk();
        $users = $response->viewData('users');
        $names = $users->pluck('user_name')->toArray();
        
        $this->assertEquals('Admin', $names[0]);
        $this->assertEquals('Alice', $names[1]);
        $this->assertEquals('Bob', $names[2]);
        $this->assertEquals('Zack', $names[3]);
    }

    /**
     * Test form displays create form.
     */
    #[Test]
    public function it_displays_create_form(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('users.form'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('users::form');
        $response->assertViewHas('user');
        
        $formUser = $response->viewData('user');
        $this->assertInstanceOf(User::class, $formUser);
        $this->assertFalse($formUser->exists);
    }

    /**
     * Test form displays edit form with existing user.
     */
    #[Test]
    public function it_displays_edit_form_with_existing_user(): void
    {
        /** Arrange */
        $adminUser = User::factory()->create();
        $editUser = User::factory()->create();

        /** Act */
        $response = $this->actingAs($adminUser)->get(route('users.form', ['id' => $editUser->user_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('users::form');
        $response->assertViewHas('user');
        
        $formUser = $response->viewData('user');
        $this->assertEquals($editUser->user_id, $formUser->user_id);
    }

    /**
     * Test form creates new user with valid data.
     */
    #[Test]
    public function it_creates_new_user_with_valid_data(): void
    {
        /** Arrange */
        $adminUser = User::factory()->create();
        
        /** @var array{user_name: string, user_email: string, user_password: string, user_type: int, btn_submit: string} $userData */
        $userData = [
            'user_name' => 'New User',
            'user_email' => 'newuser@example.com',
            'user_password' => 'password123',
            'user_type' => User::USER_TYPE_ADMINISTRATOR,
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($adminUser)->post(route('users.form'), $userData);

        /** Assert */
        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_users', [
            'user_name' => 'New User',
            'user_email' => 'newuser@example.com',
        ]);
    }

    /**
     * Test form updates existing user.
     */
    #[Test]
    public function it_updates_existing_user_with_valid_data(): void
    {
        /** Arrange */
        $adminUser = User::factory()->create();
        $editUser = User::factory()->create(['user_name' => 'Old Name']);
        
        /** @var array{user_name: string, user_email: string, btn_submit: string} $updateData */
        $updateData = [
            'user_name' => 'Updated Name',
            'user_email' => $editUser->user_email,
            'user_type' => User::USER_TYPE_ADMINISTRATOR,
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($adminUser)->post(route('users.form', ['id' => $editUser->user_id]), $updateData);

        /** Assert */
        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_users', [
            'user_id' => $editUser->user_id,
            'user_name' => 'Updated Name',
        ]);
    }

    /**
     * Test form redirects on cancel.
     */
    #[Test]
    public function it_redirects_to_index_on_cancel(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array{btn_cancel: string} $cancelData */
        $cancelData = [
            'btn_cancel' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('users.form'), $cancelData);

        /** Assert */
        $response->assertRedirect(route('users.index'));
    }

    /**
     * Test delete removes user.
     */
    #[Test]
    public function it_deletes_user(): void
    {
        /** Arrange */
        $adminUser = User::factory()->create();
        $deleteUser = User::factory()->create();
        
        /** @var array{id: int} $deleteParams */
        $deleteParams = [
            'id' => $deleteUser->user_id,
        ];

        /** Act */
        $response = $this->actingAs($adminUser)->post(route('users.delete', $deleteParams));

        /** Assert */
        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseMissing('ip_users', [
            'user_id' => $deleteUser->user_id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent user.
     */
    #[Test]
    public function it_returns_404_when_deleting_non_existent_user(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array{id: int} $deleteParams */
        $deleteParams = [
            'id' => 99999,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('users.delete', $deleteParams));

        /** Assert */
        $response->assertNotFound();
    }

    /**
     * Test form returns 404 for non-existent user in edit mode.
     */
    #[Test]
    public function it_returns_404_when_editing_non_existent_user(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('users.form', ['id' => 99999]));

        /** Assert */
        $response->assertNotFound();
    }
}
