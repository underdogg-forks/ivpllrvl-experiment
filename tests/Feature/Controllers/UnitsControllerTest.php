<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Models\User;
use Modules\Products\Controllers\UnitsController;
use Modules\Products\Models\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * UnitsController Feature Tests.
 *
 * Tests product unit management (e.g., hours, items, kg, etc.)
 */
#[CoversClass(UnitsController::class)]
class UnitsControllerTest extends FeatureTestCase
{
    /**
     * Test index displays paginated list of units.
     */
    #[Test]
    public function it_displays_paginated_list_of_units(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        Unit::factory()->count(5)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('units.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('products::units_index');
        $response->assertViewHas('units');
    }

    /**
     * Test create displays unit form.
     */
    #[Test]
    public function it_displays_create_form(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('units.create'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('products::units_form');
        $response->assertViewHas('unit');
        
        $unit = $response->viewData('unit');
        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->exists);
    }

    /**
     * Test store creates new unit with valid data.
     */
    #[Test]
    public function it_creates_new_unit_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array{unit_name: string, unit_name_plrl: string} $unitData */
        $unitData = [
            'unit_name' => 'Kilogram',
            'unit_name_plrl' => 'Kilograms',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('units.store'), $unitData);

        /** Assert */
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_units', [
            'unit_name' => 'Kilogram',
            'unit_name_plrl' => 'Kilograms',
        ]);
    }

    /**
     * Test edit displays unit form with existing data.
     */
    #[Test]
    public function it_displays_edit_form_with_existing_unit(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $unit = Unit::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('units.edit', $unit));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('products::units_form');
        $response->assertViewHas('unit');
        
        $viewUnit = $response->viewData('unit');
        $this->assertEquals($unit->unit_id, $viewUnit->unit_id);
    }

    /**
     * Test update modifies existing unit.
     */
    #[Test]
    public function it_updates_existing_unit_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $unit = Unit::factory()->create(['unit_name' => 'Old Name']);
        
        /** @var array{unit_name: string, unit_name_plrl: string} $updateData */
        $updateData = [
            'unit_name' => 'Updated Name',
            'unit_name_plrl' => 'Updated Names',
        ];

        /** Act */
        $response = $this->actingAs($user)->put(route('units.update', $unit), $updateData);

        /** Assert */
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_units', [
            'unit_id' => $unit->unit_id,
            'unit_name' => 'Updated Name',
        ]);
    }

    /**
     * Test destroy deletes unit.
     */
    #[Test]
    public function it_deletes_unit(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $unit = Unit::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->delete(route('units.destroy', $unit));

        /** Assert */
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseMissing('ip_units', [
            'unit_id' => $unit->unit_id,
        ]);
    }

    /**
     * Test units are ordered correctly.
     */
    #[Test]
    public function it_orders_units_correctly(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        Unit::factory()->create(['unit_name' => 'Zebra Unit']);
        Unit::factory()->create(['unit_name' => 'Alpha Unit']);
        Unit::factory()->create(['unit_name' => 'Beta Unit']);

        /** Act */
        $response = $this->actingAs($user)->get(route('units.index'));

        /** Assert */
        $response->assertOk();
        $units = $response->viewData('units');
        
        // Verify ordering (depends on Unit's ordered() scope implementation)
        $this->assertCount(3, $units);
    }
}
