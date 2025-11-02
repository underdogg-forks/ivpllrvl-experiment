# Test Coverage TODO

**Last Updated:** 2025-11-02
**Status:** Documentation of test coverage gaps and incomplete tests

## Overview

This document tracks:
1. Incomplete unit tests that need implementation
2. Route coverage analysis
3. Test refactoring status

## ✅ Completed: Test Readability Refactoring

**Date Completed:** 2025-11-02

All feature tests have been refactored to split `actingAs()` from HTTP method calls for improved readability:

```php
// OLD PATTERN (chained):
$response = $this->actingAs($user)->post(route('quotes.ajax.save'), $payload);

// NEW PATTERN (split):
$this->actingAs($user);
$response = $this->post(route('quotes.ajax.save'), $payload);
```

**Statistics:**
- Total feature test files processed: 47
- Files refactored: 38
- Files unchanged (already compliant): 9
- Total actingAs() calls refactored: ~271

**Benefits:**
- ✅ Improved readability
- ✅ Easier to scan for authentication patterns
- ✅ Follows single responsibility principle
- ✅ Easier debugging when tests fail

## 📋 Incomplete Unit Tests

The following unit tests are marked as incomplete and need implementation:

### Quotes Module (9 incomplete tests)

#### QuoteItemAmountServiceTest.php
- [ ] `it_calculates_item_subtotal_correctly()`
  - Status: `markTestIncomplete('Requires database setup with quote items')`
  - Needs: Database fixtures for quote items
  
- [ ] `it_applies_discount_to_item_correctly()`
  - Status: `markTestIncomplete('Requires database setup with quote items')`
  - Needs: Database fixtures with discount scenarios
  
- [ ] `it_handles_tax_calculations_for_items()`
  - Status: `markTestIncomplete('Requires database setup')`
  - Needs: Database fixtures with tax rates

#### QuoteItemServiceTest.php
- [ ] `it_creates_quote_item_with_valid_data()`
  - Status: `markTestIncomplete('Requires database setup')`
  - Needs: Database fixtures for quote items
  
- [ ] `it_updates_quote_item_successfully()`
  - Status: `markTestIncomplete('Requires database setup')`
  - Needs: Database fixtures for quote items
  
- [ ] `it_deletes_quote_item_and_recalculates_quote()`
  - Status: `markTestIncomplete('Requires database setup')`
  - Needs: Database fixtures and recalculation verification

#### QuoteServiceTest.php
- [ ] `it_generates_unique_quote_number()`
  - Status: `markTestIncomplete('Requires mocking get_setting function')`
  - Needs: Mock for global `get_setting()` function

#### QuoteTaxRateServiceTest.php
- [ ] `it_adds_tax_rate_to_quote_successfully()`
  - Status: `markTestIncomplete('Requires database setup and legacy mode')`
  - Needs: Database fixtures and legacy mode configuration
  
- [ ] `it_calculates_tax_amount_correctly()`
  - Status: `markTestIncomplete('Requires config_item mock')`
  - Needs: Mock for `config_item()` helper

#### QuoteAmountServiceTest.php
- [ ] `it_calculates_quote_total_from_items()`
  - Status: `markTestIncomplete('Requires database setup with quote items')`
  - Needs: Database fixtures with complete quote data
  
- [ ] `it_applies_discount_to_quote_total()`
  - Status: `markTestIncomplete('Requires database setup with quote data')`
  - Needs: Database fixtures with discount scenarios
  
- [ ] `it_updates_quote_amounts_in_database()`
  - Status: `markTestIncomplete('Requires database setup with quote amounts')`
  - Needs: Database fixtures and amount verification

### Products Module (1 incomplete test)

#### UnitServiceTest.php
- [ ] `it_retrieves_all_units_from_database()`
  - Status: `markTestIncomplete('Requires database setup with unit data')`
  - Needs: Database fixtures for product units

### Total Incomplete Tests: 13

## 📊 Route Coverage Analysis

**Total Routes Defined:** 203 routes across all modules

### Route Distribution by Module

Based on route file analysis:

| Module | Route Files | Estimated Routes | Test Coverage |
|--------|-------------|------------------|---------------|
| Core | Multiple | ~60 | Partial |
| Quotes | quotes.php | ~15 | Good |
| Invoices | Multiple | ~40 | Good |
| Products | Multiple | ~25 | Partial |
| Payments | Multiple | ~15 | Partial |
| CRM | Multiple | ~30 | Partial |
| Projects | Multiple | ~18 | Minimal |

### Coverage Status

**Well-Covered Routes:**
- ✅ Quotes CRUD operations
- ✅ Invoices CRUD operations
- ✅ Quote AJAX operations
- ✅ Invoice AJAX operations
- ✅ Basic CRM operations
- ✅ Basic Products operations

**Needs Coverage:**
- ⚠️ Projects module routes (minimal coverage)
- ⚠️ Some Core module routes
- ⚠️ Payment gateway routes
- ⚠️ Advanced CRM features
- ⚠️ Import/Export routes
- ⚠️ Email template routes
- ⚠️ Custom fields routes

## 🎯 Next Steps

### Priority 1: Incomplete Unit Tests (13 tests)
**Estimated Time:** 6-8 hours

For each incomplete test:
1. Create appropriate database fixtures using factories
2. Mock required global functions (`get_setting()`, `config_item()`)
3. Implement test logic following AAA pattern
4. Verify test passes
5. Remove `markTestIncomplete()` call

**Example Pattern:**
```php
#[Test]
public function it_calculates_item_subtotal_correctly(): void
{
    /** Arrange */
    $quoteItem = QuoteItem::factory()->create([
        'item_quantity' => 5,
        'item_price' => 100.00,
    ]);
    
    $service = new QuoteItemAmountService();
    
    /** Act */
    $subtotal = $service->calculateSubtotal($quoteItem);
    
    /** Assert */
    $this->assertEquals(500.00, $subtotal);
}
```

### Priority 2: Route Coverage Gap Analysis
**Estimated Time:** 4-6 hours

1. Generate complete route list: `php artisan route:list` (or equivalent)
2. Map each route to existing tests
3. Identify uncovered routes
4. Prioritize by:
   - Business criticality
   - User-facing vs. admin
   - Complexity
5. Create tests for uncovered routes

### Priority 3: Enhance Existing Tests
**Estimated Time:** 8-10 hours

1. Review existing tests for edge cases
2. Add negative test cases (error scenarios)
3. Add boundary tests (min/max values)
4. Add authorization tests (permission checks)
5. Improve assertions (test data, not just status codes)

## 📝 Guidelines for Implementing Incomplete Tests

### 1. Database Setup

Use factories to create test data:

```php
// Create with factory
$quote = Quote::factory()->create([
    'quote_status_id' => 1,
    'quote_total' => 1000.00,
]);

$item = QuoteItem::factory()->create([
    'quote_id' => $quote->quote_id,
    'item_quantity' => 2,
    'item_price' => 500.00,
]);
```

### 2. Mocking Global Functions

For legacy global functions, use PHPUnit mocking:

```php
// Mock get_setting() function
$this->mockGlobalFunction('get_setting', function($key) {
    return match($key) {
        'quote_number_format' => 'QU-{YEAR}-{NUMBER}',
        default => null,
    };
});
```

### 3. Test Organization

Follow the existing pattern:
- Unit tests in `Modules/*/Tests/Unit/`
- Feature tests in `Modules/*/Tests/Feature/`
- Use `#[CoversClass]` attribute
- Use `#[Test]` attribute
- Use `it_` prefix for test names
- Follow AAA (Arrange, Act, Assert) pattern

### 4. Running Specific Tests

```bash
# Run all incomplete tests
vendor/bin/phpunit --filter markTestIncomplete

# Run specific module tests
vendor/bin/phpunit Modules/Quotes/Tests/Unit/

# Run single test class
vendor/bin/phpunit Modules/Quotes/Tests/Unit/QuoteItemServiceTest.php

# Run single test method
vendor/bin/phpunit --filter it_creates_quote_item_with_valid_data
```

## 📈 Progress Tracking

### Incomplete Tests Progress

| Module | Total Incomplete | Completed | Remaining | % Complete |
|--------|-----------------|-----------|-----------|------------|
| Quotes | 12 | 0 | 12 | 0% |
| Products | 1 | 0 | 1 | 0% |
| **Total** | **13** | **0** | **13** | **0%** |

### Route Coverage Progress

| Category | Total Routes | Tested | Untested | % Coverage |
|----------|-------------|--------|----------|------------|
| Core CRUD | ~40 | ~25 | ~15 | 63% |
| AJAX Operations | ~30 | ~20 | ~10 | 67% |
| Admin Features | ~50 | ~20 | ~30 | 40% |
| Guest Features | ~20 | ~10 | ~10 | 50% |
| API Endpoints | ~10 | ~0 | ~10 | 0% |
| **Total** | **~150** | **~75** | **~75** | **50%** |

*Note: These are estimates. Exact numbers require route enumeration.*

## 🔍 How to Find Untested Routes

### Method 1: Manual Review
1. Check each route file in `Modules/*/routes/`
2. Search for corresponding test in `Modules/*/Tests/Feature/`
3. Document gaps

### Method 2: Automated Analysis
```bash
# Get all routes (requires Laravel-style route listing)
php artisan route:list --json > routes.json

# Get all test method names
find Modules -name "*ControllerTest.php" -exec grep -h "public function it_" {} \; > test_methods.txt

# Compare and find gaps (manual or scripted)
```

### Method 3: Code Coverage Report
```bash
# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/

# Review coverage/index.html to see untested code paths
```

## 📚 Resources

- **Testing Guide:** `PHASE-3-IMPLEMENTATION-PLAN.md`
- **Test Examples:** `Modules/Quotes/Tests/Feature/QuotesControllerTest.php`
- **Copilot Instructions:** `.github/copilot-instructions.md` (Testing section)
- **PHPUnit Docs:** https://phpunit.de/documentation.html
- **Laravel Testing:** https://laravel.com/docs/testing (for patterns)

## 🎯 Success Criteria

A test is considered "complete" when:
- [ ] No `markTestIncomplete()` calls
- [ ] Follows AAA pattern (Arrange, Act, Assert)
- [ ] Has proper PHPDoc documentation
- [ ] Uses `#[Test]` and `#[CoversClass]` attributes
- [ ] Tests data integrity, not just status codes
- [ ] Includes positive and negative test cases
- [ ] All assertions are meaningful
- [ ] Test passes consistently
- [ ] Authentication uses split pattern (`actingAs` on separate line)

## 📅 Estimated Timeline

- **Week 1:** Complete 5 incomplete unit tests
- **Week 2:** Complete remaining 8 incomplete unit tests
- **Week 3:** Route coverage analysis and gap identification
- **Week 4-6:** Implement tests for uncovered routes (prioritized)
- **Week 7-8:** Enhance existing tests with edge cases

**Total Estimated Effort:** 40-60 hours over 8 weeks
