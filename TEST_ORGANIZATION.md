# Test Organization Guide

This document describes the new test organization structure after the module refactoring.

## Directory Structure

All tests are now organized within their respective module directories:

```
Modules/
├── Core/
│   └── Tests/
│       ├── Feature/           # 17 controller tests
│       │   ├── DashboardControllerTest.php
│       │   ├── SettingsControllerTest.php
│       │   ├── CustomFieldsControllerTest.php
│       │   └── ...
│       └── Unit/              # 14 service/helper tests
│           ├── TemplateServiceTest.php
│           ├── DateHelperTest.php
│           └── ...
├── Quotes/
│   └── Tests/
│       ├── Feature/           # 3 controller tests
│       │   ├── QuotesControllerTest.php
│       │   ├── QuotesAjaxControllerTest.php
│       │   └── CrmQuotesControllerTest.php
│       └── Unit/              # 5 service tests
│           ├── QuoteServiceTest.php
│           ├── QuoteAmountServiceTest.php
│           └── ...
├── Invoices/
│   └── Tests/
│       ├── Feature/           # 9 controller tests
│       └── Unit/              # 8 service tests
├── Products/
│   └── Tests/
│       ├── Feature/           # 5 controller tests
│       └── Unit/              # 2 service tests
├── Payments/
│   └── Tests/
│       ├── Feature/           # 3 controller tests
│       └── Unit/              # 1 service test
├── Crm/
│   └── Tests/
│       ├── Feature/           # 8 controller tests
│       └── Unit/              # 1 service test
└── Projects/
    └── Tests/
        ├── Feature/           # 2 controller tests
        └── Unit/              # 2 service tests
```

## Test Statistics

### Total Tests by Module
- **Core**: 31 tests (17 Feature + 14 Unit)
- **Quotes**: 8 tests (3 Feature + 5 Unit)
- **Invoices**: 17 tests (9 Feature + 8 Unit)
- **Products**: 7 tests (5 Feature + 2 Unit)
- **Payments**: 4 tests (3 Feature + 1 Unit)
- **Crm**: 9 tests (8 Feature + 1 Unit)
- **Projects**: 4 tests (2 Feature + 2 Unit)

**Total: 80+ tests across 7 modules**

## Test Groups

All tests are now categorized into three groups using PHPUnit's `#[Group()]` attribute:

### 1. Smoke Tests (`#[Group('smoke')]`)
Basic functionality tests - the first line of defense.

**Characteristics:**
- Test index/list operations
- Test basic display/view operations
- Test simple redirects
- Quick to run
- Should always pass in a healthy system

**Examples:**
```php
#[Group('smoke')]
#[Test]
public function it_displays_list_of_products(): void
{
    $response = $this->actingAs($user)->get(route('products.index'));
    $response->assertOk();
    $response->assertViewHas('products');
}

#[Group('smoke')]
#[Test]
public function it_redirects_to_all_status_view_from_index(): void
{
    $response = $this->actingAs($user)->get(route('quotes.index'));
    $response->assertRedirect(route('quotes.status', ['status' => 'all']));
}
```

### 2. CRUD Tests (`#[Group('crud')]`)
Create, Read, Update, Delete operations and validation.

**Characteristics:**
- Test create/save operations
- Test update operations
- Test delete operations
- Test validation rules
- Test error handling

**Examples:**
```php
#[Group('crud')]
#[Test]
public function it_creates_new_invoice_and_returns_invoice_id(): void
{
    $data = ['client_id' => 1, 'invoice_number' => 'INV-001'];
    $response = $this->post(route('invoices.create'), $data);
    $response->assertOk();
}

#[Group('crud')]
#[Test]
public function it_deletes_quote_and_all_related_records(): void
{
    $quote = Quote::factory()->create();
    $response = $this->post(route('quotes.delete', $quote->quote_id));
    $this->assertDatabaseMissing('ip_quotes', ['quote_id' => $quote->quote_id]);
}
```

### 3. Exotic Tests (`#[Group('exotic')]`)
Complex business logic, special features, and edge cases.

**Characteristics:**
- Test complex calculations
- Test business rule enforcement
- Test integration between modules
- Test edge cases and corner cases
- Test unique selling points

**Examples:**
```php
#[Group('exotic')]
#[Test]
public function it_distributes_global_discount_across_items_proportionally(): void
{
    // Complex business logic testing
}

#[Group('exotic')]
#[Test]
public function it_recalculates_all_quotes_successfully(): void
{
    // Batch operation testing
}

#[Group('exotic')]
#[Test]
public function it_includes_custom_fields_in_quote_view_data(): void
{
    // Feature-specific testing
}
```

## Running Tests

### Run All Tests
```bash
php artisan test
# or
vendor/bin/phpunit
```

### Run by Test Suite Type
```bash
# Run all feature tests
php artisan test --testsuite=Feature

# Run all unit tests
php artisan test --testsuite=Unit
```

### Run by Module
```bash
# Run all Core module tests
php artisan test --testsuite=Core

# Run all Quotes module tests
php artisan test --testsuite=Quotes

# Run all Invoices module tests
php artisan test --testsuite=Invoices
```

### Run by Group
```bash
# Run all smoke tests (quick sanity check)
php artisan test --group=smoke

# Run all CRUD tests
php artisan test --group=crud

# Run all exotic tests
php artisan test --group=exotic
```

### Combined Filters
```bash
# Run smoke tests in Quotes module
php artisan test --testsuite=Quotes --group=smoke

# Run CRUD tests across all modules
php artisan test --group=crud

# Run all tests except exotic
php artisan test --exclude-group=exotic
```

### Run Specific Test File
```bash
# Run QuotesControllerTest
php artisan test Modules/Quotes/Tests/Feature/QuotesControllerTest.php

# Run specific method
php artisan test --filter=it_displays_only_draft_quotes
```

## Test Naming Conventions

### Test Method Names
All test methods use the `it_` prefix with snake_case:
```php
public function it_displays_only_draft_quotes_when_draft_status_selected(): void
public function it_creates_new_invoice_and_returns_invoice_id(): void
public function it_returns_validation_errors_when_saving_invalid_invoice(): void
```

### Test Class Names
Test classes use PascalCase with `Test` suffix:
```php
QuotesControllerTest
InvoiceServiceTest
ProductsAjaxControllerTest
```

### Data Provider Names
Data providers use camelCase with `Provider` suffix:
```php
public static function quoteStatusFilterProvider(): array
public static function validationErrorProvider(): array
public static function edgeCaseProvider(): array
```

## Test Attributes

All tests use PHPUnit 11 attributes:

```php
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(QuotesController::class)]
class QuotesControllerTest extends FeatureTestCase
{
    #[Group('smoke')]
    #[Test]
    public function it_displays_list(): void { }
    
    #[Group('crud')]
    #[Test]
    #[DataProvider('createDataProvider')]
    public function it_creates_with_data(array $data): void { }
}
```

## Base Test Classes

### tests/TestCase.php
Base Laravel TestCase for all tests.

### tests/Feature/FeatureTestCase.php
Extended by all Feature tests. Provides:
- `RefreshDatabase` trait
- `actingAs()` authentication helper
- HTTP testing capabilities

### tests/AbstractServiceTestCase.php
Extended by service tests that need common fixtures.

## Migration from Old Structure

### Old Structure
```
tests/
├── Feature/
│   └── Controllers/
│       ├── QuotesControllerTest.php
│       └── InvoicesControllerTest.php
└── Unit/
    └── Services/
        ├── Quotes/
        │   └── QuoteServiceTest.php
        └── Invoices/
            └── InvoiceServiceTest.php
```

### New Structure
```
Modules/
├── Quotes/
│   └── Tests/
│       ├── Feature/
│       │   └── QuotesControllerTest.php
│       └── Unit/
│           └── QuoteServiceTest.php
└── Invoices/
    └── Tests/
        ├── Feature/
        │   └── InvoicesControllerTest.php
        └── Unit/
            └── InvoiceServiceTest.php
```

### Namespace Changes
```php
// Old
namespace Tests\Feature\Controllers;
namespace Tests\Unit\Services\Quotes;

// New
namespace Modules\Quotes\Tests\Feature;
namespace Modules\Quotes\Tests\Unit;
```

## Best Practices

### 1. Test Organization
- Keep tests close to the code they test
- Use appropriate groups for all tests
- Follow Arrange-Act-Assert pattern

### 2. Test Independence
- Each test should be independent
- Use factories for test data
- Clean up after tests (RefreshDatabase handles this)

### 3. Test Clarity
- Descriptive test names
- Clear assertion messages
- Use data providers for repetitive tests

### 4. Test Performance
- Run smoke tests first (fastest)
- Use database transactions
- Mock external dependencies

### 5. Test Coverage
- Test happy path (smoke)
- Test error cases (crud)
- Test edge cases (exotic)

## Continuous Integration

Tests are organized to support CI/CD workflows:

```yaml
# Example GitHub Actions workflow
jobs:
  smoke:
    runs-on: ubuntu-latest
    steps:
      - run: php artisan test --group=smoke
  
  crud:
    runs-on: ubuntu-latest
    needs: smoke
    steps:
      - run: php artisan test --group=crud
  
  exotic:
    runs-on: ubuntu-latest
    needs: [smoke, crud]
    steps:
      - run: php artisan test --group=exotic
```

## Further Reading

- [DATA_PROVIDER_EXAMPLES.md](./DATA_PROVIDER_EXAMPLES.md) - Data provider patterns
- [PHASE-3-IMPLEMENTATION-PLAN.md](./PHASE-3-IMPLEMENTATION-PLAN.md) - Testing infrastructure
- [TEST-REFACTORING-GUIDE.md](./TEST-REFACTORING-GUIDE.md) - Migration guide
