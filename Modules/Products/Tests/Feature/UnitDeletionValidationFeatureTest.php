<?php

namespace Modules\Products\Tests\Feature;

use Modules\Invoices\Models\Item as InvoiceItem;
use Modules\Products\Controllers\UnitsController;
use Modules\Products\Models\Product;
use Modules\Products\Models\Unit;
use Modules\Quotes\Models\QuoteItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * UnitsController Deletion Validation Feature Tests.
 *
 * Tests HTTP endpoints for unit deletion with business rules.
 */
#[CoversClass(UnitsController::class)]
class UnitDeletionValidationFeatureTest extends FeatureTestCase
{
    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_deletes_unit_without_references(): void
    {
        /** Arrange */
        $unit = Unit::factory()->create(['unit_name' => 'Deletable Unit']);

        /** Act */
        $response = $this->delete(route('units.destroy', ['unit' => $unit->unit_id]));

        /** Assert */
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('alert_success');
        $this->assertDatabaseMissing('ip_units', ['unit_id' => $unit->unit_id]);
    }

    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_prevents_deletion_with_products(): void
    {
        /** Arrange */
        $unit = Unit::factory()->create();
        Product::factory()->create(['unit_id' => $unit->unit_id]);

        /** Act */
        $response = $this->delete(route('units.destroy', ['unit' => $unit->unit_id]));

        /** Assert */
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('alert_error');
        $this->assertDatabaseHas('ip_units', ['unit_id' => $unit->unit_id]);
    }

    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_prevents_deletion_with_invoice_items(): void
    {
        /** Arrange */
        $unit = Unit::factory()->create();
        InvoiceItem::factory()->create(['item_product_unit_id' => $unit->unit_id]);

        /** Act */
        $response = $this->delete(route('units.destroy', ['unit' => $unit->unit_id]));

        /** Assert */
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('alert_error');
        $this->assertDatabaseHas('ip_units', ['unit_id' => $unit->unit_id]);
    }

    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_prevents_deletion_with_quote_items(): void
    {
        /** Arrange */
        $unit = Unit::factory()->create();
        QuoteItem::factory()->create(['item_product_unit_id' => $unit->unit_id]);

        /** Act */
        $response = $this->delete(route('units.destroy', ['unit' => $unit->unit_id]));

        /** Assert */
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('alert_error');
        $this->assertDatabaseHas('ip_units', ['unit_id' => $unit->unit_id]);
    }

    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_prevents_deletion_with_multiple_references(): void
    {
        /** Arrange */
        $unit = Unit::factory()->create();
        
        Product::factory()->count(2)->create(['unit_id' => $unit->unit_id]);
        InvoiceItem::factory()->create(['item_product_unit_id' => $unit->unit_id]);

        /** Act */
        $response = $this->delete(route('units.destroy', ['unit' => $unit->unit_id]));

        /** Assert */
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('alert_error');
        $this->assertDatabaseHas('ip_units', ['unit_id' => $unit->unit_id]);
    }

    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_allows_deletion_after_references_removed(): void
    {
        /** Arrange */
        $unit = Unit::factory()->create();
        $product = Product::factory()->create(['unit_id' => $unit->unit_id]);

        // Initially cannot delete
        $response1 = $this->delete(route('units.destroy', ['unit' => $unit->unit_id]));
        $response1->assertSessionHas('alert_error');

        // Remove reference
        $product->delete();

        /** Act */
        $response2 = $this->delete(route('units.destroy', ['unit' => $unit->unit_id]));

        /** Assert */
        $response2->assertRedirect(route('units.index'));
        $response2->assertSessionHas('alert_success');
        $this->assertDatabaseMissing('ip_units', ['unit_id' => $unit->unit_id]);
    }
}
