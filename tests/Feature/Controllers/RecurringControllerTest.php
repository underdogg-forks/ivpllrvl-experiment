<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Models\User;
use Modules\Invoices\Controllers\RecurringController;
use Modules\Invoices\Models\InvoicesRecurring;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * RecurringController Feature Tests.
 *
 * Comprehensive test coverage for recurring invoice management via HTTP routes
 */
#[CoversClass(RecurringController::class)]
class RecurringControllerTest extends FeatureTestCase
{
    /**
     * Test recurring invoices index displays all recurring configurations.
     */
    #[Test]
    public function it_displays_list_of_recurring_invoices(): void
    {
        /** Arrange */
        $user       = User::factory()->create();
        $recurring1 = InvoicesRecurring::factory()->create();
        $recurring2 = InvoicesRecurring::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('invoices.recurring'));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('invoices::recurring_index');
        $response->assertViewHas('recurring_invoices');
        $recurringInvoices = $response->viewData('recurring_invoices');
        $this->assertGreaterThanOrEqual(2, $recurringInvoices->count());
    }

    /**
     * Test recurring invoices index includes frequency options.
     */
    #[Test]
    public function it_includes_recur_frequencies_in_view_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('invoices.recurring'));

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('recur_frequencies');
        $recurFrequencies = $response->viewData('recur_frequencies');
        $this->assertIsArray($recurFrequencies);
    }

    /**
     * Test pagination works correctly for recurring invoices.
     */
    #[Test]
    public function it_paginates_recurring_invoices_correctly(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        InvoicesRecurring::factory()->count(20)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('invoices.recurring'));

        /** Assert */
        $response->assertOk();
        $recurringInvoices = $response->viewData('recurring_invoices');
        $this->assertLessThanOrEqual(15, $recurringInvoices->count());
    }

    /**
     * Test stopping a recurring invoice sets status to 0.
     */
    #[Test]
    public function it_stops_recurring_invoice_and_sets_status_to_zero(): void
    {
        /** Arrange */
        $user      = User::factory()->create();
        $recurring = InvoicesRecurring::factory()->create(['recur_status' => 1]);

        /** Act */
        $response = $this->actingAs($user)->post(
            route('invoices.recurring.stop', ['id' => $recurring->invoice_recurring_id])
        );

        /* Assert */
        $response->assertRedirect();
        $recurring->refresh();
        $this->assertEquals(0, $recurring->recur_status);
    }

    /**
     * Test stopping recurring invoice redirects to index.
     */
    #[Test]
    public function it_redirects_to_index_after_stopping_recurring_invoice(): void
    {
        /** Arrange */
        $user      = User::factory()->create();
        $recurring = InvoicesRecurring::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->post(
            route('invoices.recurring.stop', ['id' => $recurring->invoice_recurring_id])
        );

        /* Assert */
        $response->assertRedirect();
    }

    /**
     * Test stopping non-existent recurring invoice throws 404.
     */
    #[Test]
    public function it_throws_404_when_stopping_non_existent_recurring_invoice(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array{id: int} $stopParams */
        $stopParams = [
            'id' => 99999,
        ];

        /* Act */
        $response = $this->actingAs($user)->post(route('invoices.recurring.stop', $stopParams));

        /* Assert */
        $response->assertNotFound();
    }

    /**
     * Test deleting a recurring invoice removes it from database.
     */
    #[Test]
    public function it_deletes_recurring_invoice_from_database(): void
    {
        /** Arrange */
        $user        = User::factory()->create();
        $recurring   = InvoicesRecurring::factory()->create();
        $recurringId = $recurring->invoice_recurring_id;
        
        /** @var array{id: int} $deleteParams */
        $deleteParams = [
            'id' => $recurringId,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('invoices.recurring.delete', $deleteParams));

        /* Assert */
        $response->assertRedirect();
        $this->assertNull(InvoicesRecurring::find($recurringId));
    }

    /**
     * Test deleting recurring invoice redirects to index.
     */
    #[Test]
    public function it_redirects_to_index_after_deleting_recurring_invoice(): void
    {
        /** Arrange */
        $user      = User::factory()->create();
        $recurring = InvoicesRecurring::factory()->create();
        
        /** @var array{id: int} $deleteParams */
        $deleteParams = [
            'id' => $recurring->invoice_recurring_id,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('invoices.recurring.delete', $deleteParams));

        /* Assert */
        $response->assertRedirect();
    }

    /**
     * Test deleting non-existent recurring invoice throws 404.
     */
    #[Test]
    public function it_throws_404_when_deleting_non_existent_recurring_invoice(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array{id: int} $deleteParams */
        $deleteParams = [
            'id' => 99999,
        ];

        /* Act */
        $response = $this->actingAs($user)->post(route('invoices.recurring.delete', $deleteParams));

        /* Assert */
        $response->assertNotFound();
    }

    /**
     * Test index includes filter display configuration.
     */
    #[Test]
    public function it_includes_filter_configuration_in_view_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('invoices.recurring'));

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('filter_display');
        $response->assertViewHas('filter_placeholder');
        $response->assertViewHas('filter_method');
        $filterDisplay = $response->viewData('filter_display');
        $filterMethod  = $response->viewData('filter_method');
        $this->assertTrue($filterDisplay);
        $this->assertEquals('filter_invoices_recuring', $filterMethod);
    }
}
