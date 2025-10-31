<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Models\User;
use Modules\Products\Controllers\FamiliesController;
use Modules\Products\Models\Family;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * FamiliesController Feature Tests.
 *
 * Tests product family (category) management including list, create, update, and delete.
 */
#[CoversClass(FamiliesController::class)]
class FamiliesControllerTest extends FeatureTestCase
{
    /**
     * Test index displays paginated list of families.
     */
    #[Test]
    public function it_displays_paginated_list_of_families(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        Family::factory()->count(5)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('families.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('products::families_index');
        $response->assertViewHas('families');
        $response->assertViewHas('filter_display', true);
        $response->assertViewHas('filter_placeholder');
        $response->assertViewHas('filter_method', 'filter_families');
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
        $response = $this->actingAs($user)->get(route('families.form'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('products::families_form');
        $response->assertViewHas('family');
        $response->assertViewHas('is_update', false);
        
        $family = $response->viewData('family');
        $this->assertInstanceOf(Family::class, $family);
        $this->assertFalse($family->exists);
    }

    /**
     * Test form displays edit form with existing family.
     */
    #[Test]
    public function it_displays_edit_form_with_existing_family(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $family = Family::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('families.form', ['id' => $family->family_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('products::families_form');
        $response->assertViewHas('family');
        $response->assertViewHas('is_update', true);
        
        $viewFamily = $response->viewData('family');
        $this->assertEquals($family->family_id, $viewFamily->family_id);
    }

    /**
     * Test form creates new family with valid data.
     */
    #[Test]
    public function it_creates_new_family_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array<string, mixed> $familyData */
        $familyData = [
            'family_name' => 'Electronics',
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('families.form'), $familyData);

        /** Assert */
        $response->assertRedirect(route('families.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_families', [
            'family_name' => 'Electronics',
        ]);
    }

    /**
     * Test form updates existing family.
     */
    #[Test]
    public function it_updates_existing_family_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $family = Family::factory()->create(['family_name' => 'Old Name']);
        
        /** @var array<string, mixed> $updateData */
        $updateData = [
            'family_name' => 'Updated Name',
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('families.form', ['id' => $family->family_id]), $updateData);

        /** Assert */
        $response->assertRedirect(route('families.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_families', [
            'family_id' => $family->family_id,
            'family_name' => 'Updated Name',
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
        
        /** @var array<string, mixed> $cancelData */
        $cancelData = [
            'btn_cancel' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('families.form'), $cancelData);

        /** Assert */
        $response->assertRedirect(route('families.index'));
    }

    /**
     * Test form validates required family name.
     */
    #[Test]
    public function it_validates_required_family_name(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array<string, mixed> $invalidData */
        $invalidData = [
            'family_name' => '',
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('families.form'), $invalidData);

        /** Assert */
        $response->assertSessionHasErrors('family_name');
    }

    /**
     * Test form validates unique family name.
     */
    #[Test]
    public function it_validates_unique_family_name(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        Family::factory()->create(['family_name' => 'Existing Family']);
        
        /** @var array<string, mixed> $duplicateData */
        $duplicateData = [
            'family_name' => 'Existing Family',
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('families.form'), $duplicateData);

        /** Assert */
        $response->assertSessionHasErrors('family_name');
    }

    /**
     * Test delete removes family.
     */
    #[Test]
    public function it_deletes_family(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $family = Family::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->post(route('families.delete', ['id' => $family->family_id]));

        /** Assert */
        $response->assertRedirect(route('families.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseMissing('ip_families', [
            'family_id' => $family->family_id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent family.
     */
    #[Test]
    public function it_returns_404_when_deleting_non_existent_family(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->post(route('families.delete', ['id' => 99999]));

        /** Assert */
        $response->assertNotFound();
    }
}
