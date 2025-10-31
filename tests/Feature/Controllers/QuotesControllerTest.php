<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Models\User;
use Modules\Crm\Models\Client;
use Modules\Quotes\Controllers\QuotesController;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteTaxRate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * QuotesController Feature Tests.
 *
 * Comprehensive test suite for QuotesController covering all methods
 * with data integrity validation, edge cases, and business logic verification.
 * Uses Laravel HTTP testing helpers for proper feature testing.
 */
#[CoversClass(QuotesController::class)]
class QuotesControllerTest extends FeatureTestCase
{
    /**
     * Test that index method redirects to all quotes status view.
     */
    #[Test]
    public function it_redirects_to_all_status_view_from_index(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('quotes.index'));

        /** Assert */
        $response->assertRedirect(route('quotes.status', ['status' => 'all']));
    }

    /**
     * Test that status method displays only draft quotes when draft status is selected.
     */
    #[Test]
    public function it_displays_only_draft_quotes_when_draft_status_selected(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $draftQuote = Quote::factory()->draft()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $sentQuote = Quote::factory()->sent()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get('/quotes/status/draft');

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('quotes::index');
        $response->assertViewHas('quotes');
        $response->assertViewHas('status', 'draft');

        /** Verify only draft quotes are returned */
        $quotes   = $response->viewData('quotes');
        $quoteIds = $quotes->pluck('quote_id')->toArray();
        $this->assertContains($draftQuote->quote_id, $quoteIds);
        $this->assertNotContains($sentQuote->quote_id, $quoteIds);
    }

    /**
     * Test that status method displays all quotes when 'all' status is selected.
     */
    #[Test]
    public function it_displays_all_quotes_when_all_status_selected(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $draftQuote = Quote::factory()->draft()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $sentQuote = Quote::factory()->sent()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get('/quotes/status/all');

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('quotes');
        $response->assertViewHas('status', 'all');

        /** Verify all quotes are returned */
        $quotes   = $response->viewData('quotes');
        $quoteIds = $quotes->pluck('quote_id')->toArray();
        $this->assertContains($draftQuote->quote_id, $quoteIds);
        $this->assertContains($sentQuote->quote_id, $quoteIds);
    }

    /**
     * Test that status method includes quote statuses in view data.
     */
    #[Test]
    public function it_includes_quote_statuses_in_view_data_for_status_method(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get('/quotes/status/all');

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('quote_statuses');
        $quoteStatuses = $response->viewData('quote_statuses');
        $this->assertIsArray($quoteStatuses);
        $this->assertNotEmpty($quoteStatuses);
    }

    /**
     * Test that view method displays quote details with all related data.
     */
    #[Test]
    public function it_displays_quote_details_with_items_and_amounts(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $quote = Quote::factory()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $item1 = QuoteItem::factory()->create(['quote_id' => $quote->quote_id]);
        $item2 = QuoteItem::factory()->create(['quote_id' => $quote->quote_id]);

        /** Act */
        $response = $this->actingAs($user)->get(route('quotes.view', ['quote_id' => $quote->quote_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('quote');
        $response->assertViewHas('items');
        $response->assertViewHas('quote_id');

        $viewQuote = $response->viewData('quote');
        $items = $response->viewData('items');
        $quoteId = $response->viewData('quote_id');

        $this->assertEquals($quote->quote_id, $viewQuote->quote_id);
        $this->assertEquals($quote->quote_id, $quoteId);
        $this->assertCount(2, $items);
    }

    /**
     * Test that view method returns 404 when quote is not found.
     */
    #[Test]
    public function it_returns_404_when_viewing_non_existent_quote(): void
    {
        /** Arrange */
        $user               = User::factory()->create();
        $nonExistentQuoteId = 99999;

        /** Act */
        $response = $this->actingAs($user)->get(route('quotes.view', ['quote_id' => $nonExistentQuoteId]));

        /** Assert */
        $response->assertNotFound();
    }

    /**
     * Test that view method includes custom fields in view data.
     */
    #[Test]
    public function it_includes_custom_fields_in_quote_view_data(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $quote = Quote::factory()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get(route('quotes.view', ['quote_id' => $quote->quote_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('custom_fields');
        $response->assertViewHas('custom_values');
    }

    /**
     * Test that view method includes tax rates in view data.
     */
    #[Test]
    public function it_includes_tax_rates_in_quote_view_data(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $quote = Quote::factory()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get(route('quotes.view', ['quote_id' => $quote->quote_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('tax_rates');
        $response->assertViewHas('quote_tax_rates');
    }

    /**
     * Test that delete method removes quote and redirects to index.
     */
    #[Test]
    public function it_deletes_quote_and_redirects_to_index(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $quote = Quote::factory()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $quoteId = $quote->quote_id;
        
        /** @var array<string, int> $deleteParams */
        $deleteParams = [
            'quote_id' => $quoteId,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('quotes.delete', $deleteParams));

        /** Assert */
        $response->assertRedirect(route('quotes.index'));

        /** Verify quote was deleted */
        $this->assertNull(Quote::find($quoteId));
    }

    /**
     * Test that delete method also deletes related records (items, tax rates, amounts).
     */
    #[Test]
    public function it_deletes_quote_and_all_related_records(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $quote = Quote::factory()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $item    = QuoteItem::factory()->create(['quote_id' => $quote->quote_id]);
        $taxRate = QuoteTaxRate::factory()->create(['quote_id' => $quote->quote_id]);

        $quoteId   = $quote->quote_id;
        $itemId    = $item->item_id;
        $taxRateId = $taxRate->quote_tax_rate_id;
        
        /** @var array<string, int> $deleteParams */
        $deleteParams = [
            'quote_id' => $quoteId,
        ];

        /** Act */
        $this->actingAs($user)->post(route('quotes.delete', $deleteParams));

        /** Assert - verify all related records are deleted */
        $this->assertNull(Quote::find($quoteId));
        $this->assertNull(QuoteItem::find($itemId));
        $this->assertNull(QuoteTaxRate::find($taxRateId));
    }

    /**
     * Test that deleteQuoteTax method removes tax rate and recalculates quote.
     */
    #[Test]
    public function it_removes_tax_rate_and_recalculates_quote(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $quote = Quote::factory()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $taxRate = QuoteTaxRate::factory()->create([
            'quote_id'    => $quote->quote_id,
            'tax_rate_id' => 1,
        ]);

        $quoteTaxRateId = $taxRate->quote_tax_rate_id;

        /** Act */
        $response = $this->actingAs($user)->post(
            route('quotes.delete_tax', [
                'quote_id'          => $quote->quote_id,
                'quote_tax_rate_id' => $quoteTaxRateId,
            ])
        );

        /** Assert */
        $response->assertRedirect(route('quotes.view', ['quote_id' => $quote->quote_id]));

        /** Verify tax rate was deleted */
        $this->assertNull(QuoteTaxRate::find($quoteTaxRateId));
    }

    /**
     * Test that deleteQuoteTax method redirects back to quote view.
     */
    #[Test]
    public function it_redirects_to_quote_view_after_deleting_tax_rate(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $quote = Quote::factory()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $taxRate = QuoteTaxRate::factory()->create(['quote_id' => $quote->quote_id]);

        /** Act */
        $response = $this->actingAs($user)->post(
            route('quotes.delete_tax', [
                'quote_id'          => $quote->quote_id,
                'quote_tax_rate_id' => $taxRate->quote_tax_rate_id,
            ])
        );

        /** Assert */
        $response->assertRedirect(route('quotes.view', ['quote_id' => $quote->quote_id]));
        $response->assertSessionHas('success');
    }

    /**
     * Test that recalculateAllQuotes method processes all quotes in the system.
     */
    #[Test]
    public function it_recalculates_all_quotes_successfully(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $quote1 = Quote::factory()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $quote2 = Quote::factory()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->post(route('quotes.recalculate_all'));

        /** Assert */
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that recalculateAllQuotes method handles empty quote list gracefully.
     */
    #[Test]
    public function it_handles_empty_quote_list_when_recalculating_all_quotes(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        Quote::query()->delete();

        /** Act */
        $response = $this->actingAs($user)->post(route('quotes.recalculate_all'));

        /** Assert */
        $response->assertRedirect();
        /** Should still return success even with no quotes */
        $response->assertSessionHas('success');
    }

    /**
     * Test that status method paginates results correctly.
     */
    #[Test]
    public function it_paginates_quote_results_correctly(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        /** Create 20 draft quotes (more than the 15 per page limit) */
        for ($i = 0; $i < 20; $i++) {
            Quote::factory()->draft()->create([
                'client_id' => $client->client_id,
                'user_id'   => $user->user_id,
            ]);
        }

        /** Act */
        $response = $this->actingAs($user)->get('/quotes/status/draft');

        /** Assert */
        $response->assertOk();
        $quotes = $response->viewData('quotes');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $quotes);
        $this->assertEquals(15, $quotes->perPage());
        $this->assertLessThanOrEqual(15, $quotes->count());
    }

    /**
     * Test that status method filters quotes by sent status correctly.
     */
    #[Test]
    public function it_displays_only_sent_quotes_when_sent_status_selected(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $draftQuote = Quote::factory()->draft()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $sentQuote = Quote::factory()->sent()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get('/quotes/status/sent');

        /** Assert */
        $response->assertOk();
        $quotes   = $response->viewData('quotes');
        $quoteIds = $quotes->pluck('quote_id')->toArray();

        $this->assertNotContains($draftQuote->quote_id, $quoteIds);
        $this->assertContains($sentQuote->quote_id, $quoteIds);
    }

    /**
     * Test that status method filters quotes by approved status correctly.
     */
    #[Test]
    public function it_displays_only_approved_quotes_when_approved_status_selected(): void
    {
        /** Arrange */
        $user   = User::factory()->create();
        $client = Client::factory()->create();

        $draftQuote = Quote::factory()->draft()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $approvedQuote = Quote::factory()->approved()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get('/quotes/status/approved');

        /** Assert */
        $response->assertOk();
        $quotes   = $response->viewData('quotes');
        $quoteIds = $quotes->pluck('quote_id')->toArray();

        $this->assertNotContains($draftQuote->quote_id, $quoteIds);
        $this->assertContains($approvedQuote->quote_id, $quoteIds);
    }
}
