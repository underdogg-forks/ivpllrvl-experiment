# Data Provider Examples

This document demonstrates how to use PHPUnit data providers to reduce code duplication in tests.

## Example 1: Status Filtering Tests (Before and After)

### Before (Without Data Provider)
```php
#[Group('smoke')]
#[Test]
public function it_displays_only_draft_quotes_when_draft_status_selected(): void
{
    /** Arrange */
    $user = User::factory()->create();
    $client = Client::factory()->create();
    
    $draftQuote = Quote::factory()->draft()->create([
        'client_id' => $client->client_id,
        'user_id' => $user->user_id,
    ]);
    
    $sentQuote = Quote::factory()->sent()->create([
        'client_id' => $client->client_id,
        'user_id' => $user->user_id,
    ]);
    
    /** Act */
    $response = $this->actingAs($user)->get('/quotes/status/draft');
    
    /** Assert */
    $response->assertOk();
    $quotes = $response->viewData('quotes');
    $quoteIds = $quotes->pluck('quote_id')->toArray();
    $this->assertContains($draftQuote->quote_id, $quoteIds);
    $this->assertNotContains($sentQuote->quote_id, $quoteIds);
}

#[Group('smoke')]
#[Test]
public function it_displays_only_sent_quotes_when_sent_status_selected(): void
{
    /** Arrange */
    $user = User::factory()->create();
    $client = Client::factory()->create();
    
    $draftQuote = Quote::factory()->draft()->create([
        'client_id' => $client->client_id,
        'user_id' => $user->user_id,
    ]);
    
    $sentQuote = Quote::factory()->sent()->create([
        'client_id' => $client->client_id,
        'user_id' => $user->user_id,
    ]);
    
    /** Act */
    $response = $this->actingAs($user)->get('/quotes/status/sent');
    
    /** Assert */
    $response->assertOk();
    $quotes = $response->viewData('quotes');
    $quoteIds = $quotes->pluck('quote_id')->toArray();
    $this->assertNotContains($draftQuote->quote_id, $quoteIds);
    $this->assertContains($sentQuote->quote_id, $quoteIds);
}
```

### After (With Data Provider)
```php
/**
 * Data provider for quote status filtering tests.
 *
 * @return array<string, array{status: string, factoryMethod: string, shouldInclude: array<string>, shouldExclude: array<string>}>
 */
public static function quoteStatusFilterProvider(): array
{
    return [
        'draft status filters draft quotes' => [
            'status' => 'draft',
            'factoryMethod' => 'draft',
            'shouldInclude' => ['draft'],
            'shouldExclude' => ['sent', 'approved'],
        ],
        'sent status filters sent quotes' => [
            'status' => 'sent',
            'factoryMethod' => 'sent',
            'shouldInclude' => ['sent'],
            'shouldExclude' => ['draft', 'approved'],
        ],
        'approved status filters approved quotes' => [
            'status' => 'approved',
            'factoryMethod' => 'approved',
            'shouldInclude' => ['approved'],
            'shouldExclude' => ['draft', 'sent'],
        ],
    ];
}

/**
 * Test that status method correctly filters quotes by status.
 *
 * @dataProvider quoteStatusFilterProvider
 */
#[Group('smoke')]
#[Test]
#[DataProvider('quoteStatusFilterProvider')]
public function it_displays_only_quotes_matching_selected_status(
    string $status,
    string $factoryMethod,
    array $shouldInclude,
    array $shouldExclude
): void {
    /** Arrange */
    $user = User::factory()->create();
    $client = Client::factory()->create();
    
    // Create quotes for each status
    $quotes = [
        'draft' => Quote::factory()->draft()->create([
            'client_id' => $client->client_id,
            'user_id' => $user->user_id,
        ]),
        'sent' => Quote::factory()->sent()->create([
            'client_id' => $client->client_id,
            'user_id' => $user->user_id,
        ]),
        'approved' => Quote::factory()->approved()->create([
            'client_id' => $client->client_id,
            'user_id' => $user->user_id,
        ]),
    ];
    
    /** Act */
    $response = $this->actingAs($user)->get("/quotes/status/{$status}");
    
    /** Assert */
    $response->assertOk();
    $response->assertViewHas('quotes');
    $response->assertViewHas('status', $status);
    
    $returnedQuotes = $response->viewData('quotes');
    $quoteIds = $returnedQuotes->pluck('quote_id')->toArray();
    
    // Verify included quotes
    foreach ($shouldInclude as $includeStatus) {
        $this->assertContains(
            $quotes[$includeStatus]->quote_id,
            $quoteIds,
            "Expected {$includeStatus} quote to be included when filtering by {$status}"
        );
    }
    
    // Verify excluded quotes
    foreach ($shouldExclude as $excludeStatus) {
        $this->assertNotContains(
            $quotes[$excludeStatus]->quote_id,
            $quoteIds,
            "Expected {$excludeStatus} quote to be excluded when filtering by {$status}"
        );
    }
}
```

## Example 2: Validation Tests with Data Providers

### Before
```php
#[Test]
public function it_returns_validation_error_when_client_id_is_missing(): void
{
    $response = $this->post('/invoices', ['invoice_number' => 'INV-001']);
    $response->assertSessionHasErrors('client_id');
}

#[Test]
public function it_returns_validation_error_when_invoice_number_is_missing(): void
{
    $response = $this->post('/invoices', ['client_id' => 1]);
    $response->assertSessionHasErrors('invoice_number');
}
```

### After
```php
public static function validationErrorProvider(): array
{
    return [
        'missing client_id' => [
            'data' => ['invoice_number' => 'INV-001'],
            'errorField' => 'client_id',
        ],
        'missing invoice_number' => [
            'data' => ['client_id' => 1],
            'errorField' => 'invoice_number',
        ],
        'invalid date format' => [
            'data' => ['client_id' => 1, 'invoice_number' => 'INV-001', 'invoice_date' => 'not-a-date'],
            'errorField' => 'invoice_date',
        ],
    ];
}

#[Test]
#[DataProvider('validationErrorProvider')]
public function it_returns_validation_error_for_invalid_data(array $data, string $errorField): void
{
    $response = $this->post('/invoices', $data);
    $response->assertSessionHasErrors($errorField);
}
```

## Benefits of Data Providers

1. **Reduced Code Duplication**: Write test logic once, test multiple scenarios
2. **Better Test Organization**: Related tests grouped together
3. **Easier Maintenance**: Change logic in one place
4. **Clearer Test Intent**: Data provider names describe scenarios
5. **More Test Coverage**: Easy to add new test cases

## When to Use Data Providers

✅ **Good Use Cases:**
- Testing multiple input variations
- Testing different status/state combinations
- Testing validation rules
- Testing different user permissions
- Testing edge cases with various inputs

❌ **Not Recommended:**
- Tests with completely different setup/teardown
- Tests with different assertions
- Tests that are already simple and clear
- One-off test cases

## Migration Strategy

When refactoring existing tests to use data providers:

1. Identify repetitive test patterns
2. Extract common test logic
3. Create data provider with descriptive keys
4. Replace individual tests with parameterized test
5. Verify all scenarios still pass
6. Add new test cases easily to the provider
