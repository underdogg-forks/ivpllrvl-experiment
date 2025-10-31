# Test Refactoring Guide

This document provides comprehensive guidance for refactoring the remaining feature tests to use Laravel HTTP testing helpers.

## Changes Made

### Base Test Classes Created

1. **tests/TestCase.php** - Base Laravel TestCase
   - Extends `Illuminate\Foundation\Testing\TestCase`
   - Bootstraps Laravel application

2. **tests/Feature/FeatureTestCase.php** - Feature test base
   - Uses `RefreshDatabase` trait
   - Provides `actingAsUser()` helper for authentication

3. **tests/Unit/UnitTestCase.php** - Unit test base  
   - Provides shared fixtures: `createTestInvoice()`, `createTestItem()`, `createTestQuote()`
   - Provides `cleanupTables()` for database cleanup

### Refactored Tests

- ✅ **QuotesControllerTest** (17 tests) - Complete

## Refactoring Pattern

### Before (Direct Controller Instantiation)
```php
use PHPUnit\Framework\TestCase;

class QuotesControllerTest extends TestCase
{
    #[Test]
    public function it_displays_only_draft_quotes_when_draft_status_selected(): void
    {
        /** Arrange */
        $client = Client::factory()->create();
        $user   = User::factory()->create();

        $draftQuote = Quote::factory()->draft()->create([
            'client_id' => $client->client_id,
            'user_id'   => $user->user_id,
        ]);

        $controller = new QuotesController();

        /** Act */
        $response = $controller->status('draft');

        /* Assert */
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $viewData = $response->getData();

        $this->assertArrayHasKey('quotes', $viewData);
        $quoteIds = $viewData['quotes']->pluck('quote_id')->toArray();
        $this->assertContains($draftQuote->quote_id, $quoteIds);
    }
}
```

### After (Laravel HTTP Testing)
```php
use Tests\Feature\FeatureTestCase;

class QuotesControllerTest extends FeatureTestCase
{
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

        /** Act */
        $response = $this->actingAs($user)->get('/quotes/status/draft');

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('quotes');

        $quotes   = $response->viewData('quotes');
        $quoteIds = $quotes->pluck('quote_id')->toArray();
        $this->assertContains($draftQuote->quote_id, $quoteIds);
    }
}
```

## Key Changes

### 1. Base Class
```php
// Before
use PHPUnit\Framework\TestCase;
class MyTest extends TestCase

// After
use Tests\Feature\FeatureTestCase;
class MyTest extends FeatureTestCase
```

### 2. Remove Controller Instantiation
```php
// Before
$controller = new MyController();
$response = $controller->method();

// After
$response = $this->actingAs($user)->get('/route');
```

### 3. Assertions

**HTTP Status:**
```php
// Before
$this->assertInstanceOf(\Illuminate\View\View::class, $response);

// After
$response->assertOk();  // or assertRedirect(), assertNotFound()
```

**View Data:**
```php
// Before
$viewData = $response->getData();
$this->assertArrayHasKey('quotes', $viewData);

// After
$response->assertViewHas('quotes');
$quotes = $response->viewData('quotes');
```

**Redirects:**
```php
// Before
$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
$this->assertEquals(route('quotes.index'), $response->getTargetUrl());

// After
$response->assertRedirect(route('quotes.index'));
```

**Session:**
```php
// Before
$this->assertTrue($response->getSession()->has('success'));

// After
$response->assertSessionHas('success');
```

### 4. HTTP Methods

**GET Requests:**
```php
$response = $this->actingAs($user)->get('/quotes/status/draft');
$response = $this->actingAs($user)->get(route('quotes.view', ['id' => $quoteId]));
```

**POST Requests:**
```php
$response = $this->actingAs($user)->post(route('quotes.delete', ['id' => $quoteId]));
$response = $this->actingAs($user)->postJson('/api/quotes', $data);
```

**Other Methods:**
```php
$this->actingAs($user)->put('/quotes/1', $data);
$this->actingAs($user)->patch('/quotes/1', $data);
$this->actingAs($user)->delete('/quotes/1');
```

## Checklist for Each Test File

- [ ] Change base class from `TestCase` to `FeatureTestCase`
- [ ] Add `use Tests\Feature\FeatureTestCase;`
- [ ] Remove `use PHPUnit\Framework\TestCase;`
- [ ] Remove all `$controller = new Controller();` lines
- [ ] Replace controller calls with HTTP calls (`$this->actingAs()->get()`)
- [ ] Update assertions to use Laravel test helpers
- [ ] Verify routes exist for all tested methods
- [ ] Add missing routes if needed
- [ ] Run tests to verify no regressions

## Files Remaining

1. `tests/Feature/Controllers/QuotesAjaxControllerTest.php` - 18 tests
2. `tests/Feature/Controllers/InvoicesControllerTest.php` - 20 tests
3. `tests/Feature/Controllers/InvoicesAjaxControllerTest.php` - 22 tests
4. `tests/Feature/Controllers/ProductsControllerTest.php` - 17 tests
5. `tests/Feature/Controllers/InvoiceGroupsControllerTest.php` - 14 tests
6. `tests/Feature/Controllers/RecurringControllerTest.php` - 8 tests
7. `tests/Feature/Controllers/CronControllerTest.php` - 11 tests

## Route Requirements

Before refactoring a test, check that routes exist:

```bash
# Check routes for a controller
grep "ControllerName" Modules/*/routes/web/*.php

# List all routes
php artisan route:list
```

If routes are missing, add them before refactoring tests.

## Unit Tests

Unit tests don't need HTTP testing (they test services/models directly).
However, they can benefit from:

1. Using `UnitTestCase` base class for shared fixtures
2. Using helper methods like `createTestInvoice()`

Example:
```php
use Tests\Unit\UnitTestCase;

class InvoiceServiceTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cleanupTables([
            'ip_invoices',
            'ip_invoice_items',
            'ip_invoice_amounts',
        ]);
    }
    
    #[Test]
    public function it_calculates_invoice_total(): void
    {
        // Use fixture helper
        $invoice = $this->createTestInvoice(['invoice_status_id' => 1]);
        $item = $this->createTestItem($invoice->invoice_id, ['item_price' => 100]);
        
        // Test logic...
    }
}
```

## Benefits of Refactoring

1. **True feature tests** - Tests exercise the full HTTP stack
2. **Better test isolation** - RefreshDatabase ensures clean state
3. **Route validation** - Tests fail if routes are missing/broken
4. **Authentication testing** - Can test with/without auth
5. **HTTP-specific testing** - Can test headers, cookies, sessions
6. **Laravel conventions** - Follows Laravel testing best practices

## Common Pitfalls

1. **Forgetting authentication** - Most routes require `actingAs($user)`
2. **Wrong HTTP method** - Use POST for deletes, not GET
3. **Missing routes** - Add routes before refactoring tests
4. **Route parameters** - Check parameter names match route definitions
5. **View names** - View assertions need exact view names

## Example: Complete Test Refactoring

See `tests/Feature/Controllers/QuotesControllerTest.php` for a complete example of all 17 methods refactored.

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=QuotesControllerTest

# Run with coverage
php artisan test --coverage

# Run unit tests only
php artisan test --testsuite=Unit

# Run feature tests only
php artisan test --testsuite=Feature
```
