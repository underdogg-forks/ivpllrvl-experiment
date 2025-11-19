<?php

namespace Modules\Products\Tests\Feature;

use Modules\Products\Controllers\FamiliesController;
use Modules\Products\Models\Family;
use Modules\Products\Models\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * FamiliesController Deletion Validation Feature Tests.
 *
 * Tests HTTP endpoints for family deletion with business rules.
 */
#[CoversClass(FamiliesController::class)]
class FamilyDeletionValidationFeatureTest extends FeatureTestCase
{
    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_deletes_family_without_products(): void
    {
        /** Arrange */
        $family = Family::factory()->create(['family_name' => 'Empty Family']);

        /** Act */
        $response = $this->post(route('families.delete', ['id' => $family->family_id]));

        /* Assert */
        $response->assertRedirect(route('families.index'));
        $response->assertSessionHas('alert_success');
        $this->assertDatabaseMissing('ip_families', ['family_id' => $family->family_id]);
    }

    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_prevents_deletion_with_products(): void
    {
        /** Arrange */
        $family = Family::factory()->create();
        Product::factory()->create(['family_id' => $family->family_id]);

        /** Act */
        $response = $this->post(route('families.delete', ['id' => $family->family_id]));

        /* Assert */
        $response->assertRedirect(route('families.index'));
        $response->assertSessionHas('alert_error');
        $this->assertDatabaseHas('ip_families', ['family_id' => $family->family_id]);
    }

    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_prevents_deletion_with_multiple_products(): void
    {
        /** Arrange */
        $family = Family::factory()->create();
        Product::factory()->count(3)->create(['family_id' => $family->family_id]);

        /** Act */
        $response = $this->post(route('families.delete', ['id' => $family->family_id]));

        /* Assert */
        $response->assertRedirect(route('families.index'));
        $response->assertSessionHas('alert_error');
        $this->assertDatabaseHas('ip_families', ['family_id' => $family->family_id]);
    }

    #[Group('validation')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_handles_invalid_family_id(): void
    {
        /** Arrange */
        $invalidId = -1;

        /** Act */
        $response = $this->post(route('families.delete', ['id' => $invalidId]));

        /* Assert */
        $response->assertRedirect(route('families.index'));
        $response->assertSessionHas('alert_error');
    }

    #[Group('validation')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_handles_nonexistent_family_id(): void
    {
        /** Arrange */
        $nonexistentId = 99999;

        /** Act */
        $response = $this->post(route('families.delete', ['id' => $nonexistentId]));

        /* Assert */
        $response->assertRedirect(route('families.index'));
        $response->assertSessionHas('alert_error');
    }

    #[Group('business-rules')]
    #[Group('deletion')]
    #[Group('http')]
    #[Test]
    public function it_allows_deletion_after_products_removed(): void
    {
        /** Arrange */
        $family  = Family::factory()->create();
        $product = Product::factory()->create(['family_id' => $family->family_id]);

        // Initially cannot delete
        $response1 = $this->post(route('families.delete', ['id' => $family->family_id]));
        $response1->assertSessionHas('alert_error');

        // Remove product
        $product->delete();

        /** Act */
        $response2 = $this->post(route('families.delete', ['id' => $family->family_id]));

        /* Assert */
        $response2->assertRedirect(route('families.index'));
        $response2->assertSessionHas('alert_success');
        $this->assertDatabaseMissing('ip_families', ['family_id' => $family->family_id]);
    }
}
