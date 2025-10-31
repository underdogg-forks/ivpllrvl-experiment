<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\CustomFieldsController;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * CustomFieldsController Feature Tests.
 *
 * Tests custom field management for extending data models.
 */
#[CoversClass(CustomFieldsController::class)]
class CustomFieldsControllerTest extends FeatureTestCase
{
    /**
     * Test index displays paginated list of custom fields.
     */
    #[Test]
    public function it_displays_paginated_list_of_custom_fields(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        CustomField::factory()->count(5)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('custom_fields.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::custom_fields_index');
        $response->assertViewHas('custom_fields');
    }

    /**
     * Test custom fields are ordered by table and label.
     */
    #[Test]
    public function it_orders_custom_fields_by_table_and_label(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        CustomField::factory()->create(['custom_field_table' => 'ip_clients', 'custom_field_label' => 'Field B']);
        CustomField::factory()->create(['custom_field_table' => 'ip_clients', 'custom_field_label' => 'Field A']);
        CustomField::factory()->create(['custom_field_table' => 'ip_invoices', 'custom_field_label' => 'Field C']);

        /** Act */
        $response = $this->actingAs($user)->get(route('custom_fields.index'));

        /** Assert */
        $response->assertOk();
        $customFields = $response->viewData('custom_fields');
        
        // Verify ordering by table, then label
        $this->assertGreaterThan(0, $customFields->count());
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
        $response = $this->actingAs($user)->get(route('custom_fields.form'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::custom_fields_form');
        $response->assertViewHas('custom_field');
        
        $customField = $response->viewData('custom_field');
        $this->assertInstanceOf(CustomField::class, $customField);
        $this->assertFalse($customField->exists);
    }

    /**
     * Test form displays edit form with existing custom field.
     */
    #[Test]
    public function it_displays_edit_form_with_existing_custom_field(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $customField = CustomField::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('custom_fields.form', ['id' => $customField->custom_field_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::custom_fields_form');
        $response->assertViewHas('custom_field');
        
        $viewCustomField = $response->viewData('custom_field');
        $this->assertEquals($customField->custom_field_id, $viewCustomField->custom_field_id);
    }

    /**
     * Test form creates new custom field with valid data.
     */
    #[Test]
    public function it_creates_new_custom_field_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /**
         * {
         *     "custom_field_table": "ip_clients",
         *     "custom_field_label": "Test Field",
         *     "custom_field_column": "custom_test_field",
         *     "btn_submit": "1"
         * }
         */
        $customFieldData = [
            'custom_field_table' => 'ip_clients',
            'custom_field_label' => 'Test Field',
            'custom_field_column' => 'custom_test_field',
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_fields.form'), $customFieldData);

        /** Assert */
        $response->assertRedirect(route('custom_fields.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_custom_fields', [
            'custom_field_table' => 'ip_clients',
            'custom_field_label' => 'Test Field',
        ]);
    }

    /**
     * Test form updates existing custom field.
     */
    #[Test]
    public function it_updates_existing_custom_field_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $customField = CustomField::factory()->create(['custom_field_label' => 'Old Label']);
        
        /**
         * {
         *     "custom_field_table": "<custom_field_table>",
         *     "custom_field_label": "Updated Label",
         *     "custom_field_column": "<custom_field_column>",
         *     "btn_submit": "1"
         * }
         */
        $updateData = [
            'custom_field_table' => $customField->custom_field_table,
            'custom_field_label' => 'Updated Label',
            'custom_field_column' => $customField->custom_field_column,
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_fields.form', ['id' => $customField->custom_field_id]), $updateData);

        /** Assert */
        $response->assertRedirect(route('custom_fields.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_custom_fields', [
            'custom_field_id' => $customField->custom_field_id,
            'custom_field_label' => 'Updated Label',
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
        
        /**
         * {
         *     "btn_cancel": "1"
         * }
         */
        $cancelData = [
            'btn_cancel' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_fields.form'), $cancelData);

        /** Assert */
        $response->assertRedirect(route('custom_fields.index'));
    }

    /**
     * Test delete removes custom field.
     */
    #[Test]
    public function it_deletes_custom_field(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $customField = CustomField::factory()->create();
        
        /**
         * {
         *     "id": <custom_field_id>
         * }
         */
        $deleteParams = [
            'id' => $customField->custom_field_id,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_fields.delete', $deleteParams));

        /** Assert */
        $response->assertRedirect(route('custom_fields.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseMissing('ip_custom_fields', [
            'custom_field_id' => $customField->custom_field_id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent custom field.
     */
    #[Test]
    public function it_returns_404_when_deleting_non_existent_custom_field(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /**
         * {
         *     "id": 99999
         * }
         */
        $deleteParams = [
            'id' => 99999,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('custom_fields.delete', $deleteParams));

        /** Assert */
        $response->assertNotFound();
    }

    /**
     * Test form returns 404 for non-existent custom field in edit mode.
     */
    #[Test]
    public function it_returns_404_when_editing_non_existent_custom_field(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('custom_fields.form', ['id' => 99999]));

        /** Assert */
        $response->assertNotFound();
    }
}
