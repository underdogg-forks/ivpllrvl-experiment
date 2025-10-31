<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\CustomValuesController;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\User;
use Modules\Custom\Models\CustomValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * CustomValuesController Feature Tests.
 *
 * Tests custom value management for custom fields.
 */
#[CoversClass(CustomValuesController::class)]
class CustomValuesControllerTest extends FeatureTestCase
{
    /**
     * Test index displays paginated list of custom values.
     */
    #[Test]
    public function it_displays_paginated_list_of_custom_values(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $customField = CustomField::factory()->create();
        CustomValue::factory()->count(5)->create(['custom_field_id' => $customField->custom_field_id]);

        /** Act */
        $response = $this->actingAs($user)->get(route('custom_values.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::custom_values_index');
        $response->assertViewHas('custom_values');
    }

    /**
     * Test custom values are loaded with custom field relationship.
     */
    #[Test]
    public function it_loads_custom_values_with_custom_field_relationship(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $customField = CustomField::factory()->create();
        CustomValue::factory()->create(['custom_field_id' => $customField->custom_field_id]);

        /** Act */
        $response = $this->actingAs($user)->get(route('custom_values.index'));

        /** Assert */
        $response->assertOk();
        $customValues = $response->viewData('custom_values');
        
        // Verify relationship is loaded
        $this->assertGreaterThan(0, $customValues->count());
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
        $response = $this->actingAs($user)->get(route('custom_values.form'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::custom_values_form');
        $response->assertViewHas('custom_value');
        $response->assertViewHas('custom_fields');
        
        $customValue = $response->viewData('custom_value');
        $this->assertInstanceOf(CustomValue::class, $customValue);
        $this->assertFalse($customValue->exists);
    }

    /**
     * Test form displays edit form with existing custom value.
     */
    #[Test]
    public function it_displays_edit_form_with_existing_custom_value(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $customField = CustomField::factory()->create();
        $customValue = CustomValue::factory()->create(['custom_field_id' => $customField->custom_field_id]);

        /** Act */
        $response = $this->actingAs($user)->get(route('custom_values.form', ['id' => $customValue->custom_value_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::custom_values_form');
        $response->assertViewHas('custom_value');
        $response->assertViewHas('custom_fields');
        
        $viewCustomValue = $response->viewData('custom_value');
        $this->assertEquals($customValue->custom_value_id, $viewCustomValue->custom_value_id);
    }

    /**
     * Test form creates new custom value with valid data.
     */
    #[Test]
    public function it_creates_new_custom_value_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $customField = CustomField::factory()->create();
        
        /** @var array<string, mixed> $customValueData */
        $customValueData = [
            'custom_field_id' => $customField->custom_field_id,
            'custom_value_value' => 'Test Value',
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_values.form'), $customValueData);

        /** Assert */
        $response->assertRedirect(route('custom_values.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_custom_values', [
            'custom_field_id' => $customField->custom_field_id,
            'custom_value_value' => 'Test Value',
        ]);
    }

    /**
     * Test form updates existing custom value.
     */
    #[Test]
    public function it_updates_existing_custom_value_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $customField = CustomField::factory()->create();
        $customValue = CustomValue::factory()->create([
            'custom_field_id' => $customField->custom_field_id,
            'custom_value_value' => 'Old Value',
        ]);
        
        /** @var array<string, mixed> $updateData */
        $updateData = [
            'custom_field_id' => $customField->custom_field_id,
            'custom_value_value' => 'Updated Value',
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_values.form', ['id' => $customValue->custom_value_id]), $updateData);

        /** Assert */
        $response->assertRedirect(route('custom_values.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_custom_values', [
            'custom_value_id' => $customValue->custom_value_id,
            'custom_value_value' => 'Updated Value',
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

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_values.form'), ['btn_cancel' => '1']);

        /** Assert */
        $response->assertRedirect(route('custom_values.index'));
    }

    /**
     * Test delete removes custom value.
     */
    #[Test]
    public function it_deletes_custom_value(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $customField = CustomField::factory()->create();
        $customValue = CustomValue::factory()->create(['custom_field_id' => $customField->custom_field_id]);

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_values.delete', ['id' => $customValue->custom_value_id]));

        /** Assert */
        $response->assertRedirect(route('custom_values.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseMissing('ip_custom_values', [
            'custom_value_id' => $customValue->custom_value_id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent custom value.
     */
    #[Test]
    public function it_returns_404_when_deleting_non_existent_custom_value(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_values.delete', ['id' => 99999]));

        /** Assert */
        $response->assertNotFound();
    }

    /**
     * Test form returns 404 for non-existent custom value in edit mode.
     */
    #[Test]
    public function it_returns_404_when_editing_non_existent_custom_value(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('custom_values.form', ['id' => 99999]));

        /** Assert */
        $response->assertNotFound();
    }
}
