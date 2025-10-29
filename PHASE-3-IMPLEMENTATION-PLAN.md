# Phase 3: Controller Migration - Implementation Plan

**Date:** 2025-10-29
**Status:** Infrastructure Setup Complete, Implementation Plan Defined

---

## Overview

Phase 3 involves migrating 44 CodeIgniter controllers to PSR-4 compliant Laravel/Illuminate controllers with comprehensive feature tests.

---

## Testing Infrastructure ✅

### PHPUnit Configuration
- ✅ PHPUnit 11.x added to composer.json
- ✅ `phpunit.xml` created with proper configuration
- ✅ `tests/bootstrap.php` for Illuminate initialization
- ✅ Test directory structure: `tests/Feature/Controllers/`

### Test Standards
All tests must follow these standards:

**Test Method Naming:**
```php
public function it_displays_list_of_quotes_when_user_is_authenticated()
public function it_creates_new_quote_with_valid_data()
public function it_returns_404_when_quote_not_found()
```

**Test Structure:**
```php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(QuotesController::class)]
class QuotesControllerTest extends TestCase
{
    #[Test]
    public function it_displays_list_of_quotes_when_user_is_authenticated(): void
    {
        // Arrange
        $expectedQuotes = [...];
        
        // Act
        $response = $this->get('/quotes');
        
        // Assert
        $this->assertStatus(200);
        $this->assertViewHas('quotes', $expectedQuotes);
    }
}
```

---

## Controller Migration Pattern

### PHPDoc Requirements

Each migrated method must include:

```php
/**
 * Display quotes filtered by status
 * 
 * Migrates the status filtering functionality from the legacy Quotes controller.
 * Users can view quotes by status: draft, sent, viewed, approved, rejected, or all.
 * 
 * @param string $status The status filter (draft|sent|viewed|approved|rejected|all)
 * @param int $page The pagination page number
 * @return \Illuminate\View\View
 * 
 * @legacy-function status
 * @legacy-file application/modules/quotes/controllers/Quotes.php
 * @legacy-line 38
 */
public function status(string $status = 'all', int $page = 0)
{
    // Implementation
}
```

### Conversion Patterns

**1. Model Loading:**
```php
// Before (CodeIgniter)
$this->load->model('mdl_quotes');
$quotes = $this->mdl_quotes->get();

// After (Eloquent)
use Modules\Quotes\Entities\Quote;
$quotes = Quote::all();
```

**2. Input Handling:**
```php
// Before (CodeIgniter)
$data = $this->input->post();

// After (Laravel)
use Illuminate\Http\Request;
$data = $request->all();
```

**3. Validation:**
```php
// Before (CodeIgniter)
$this->form_validation->set_rules('quote_name', 'Quote Name', 'required');
if ($this->form_validation->run() === false) {
    // Handle error
}

// After (Laravel)
$validated = $request->validate([
    'quote_name' => 'required|string|max:255',
]);
```

**4. Views:**
```php
// Before (CodeIgniter)
$this->layout->buffer('content', 'quotes/index');
$this->layout->render();

// After (Laravel/Illuminate)
return view('quotes::index', $data);
```

**5. Redirects:**
```php
// Before (CodeIgniter)
redirect('quotes/view/' . $quote_id);

// After (Laravel)
return redirect()->route('quotes.view', ['id' => $quote_id]);
```

**6. Flash Messages:**
```php
// Before (CodeIgniter)
$this->session->set_flashdata('alert_success', 'Quote created!');

// After (Laravel)
return redirect()->with('success', 'Quote created!');
```

---

## Controllers to Migrate (44 total)

### Priority 1: Core Business (15 controllers)

**Quotes Module (2):**
1. ✅ QuotesController - 8 methods
   - index(), status(), view(), delete(), generate_pdf(), delete_quote_tax(), recalculate_all_quotes()
2. ⏳ QuotesAjaxController - Ajax operations

**Invoices Module (5):**
3. ⏳ InvoicesController - 15+ methods
   - index(), status(), view(), create(), edit(), delete(), generate_pdf(), archive(), download()
4. ⏳ InvoicesAjaxController - Ajax operations
5. ⏳ InvoicesCronController - Cron jobs
6. ⏳ RecurringController - Recurring invoices
7. ⏳ InvoiceGroupsController - Number generation

**CRM Module (8):**
8. ⏳ ClientsController - Client management
9. ⏳ ClientNotesController - Client notes
10. ⏳ ProjectsController - Project management
11. ⏳ TasksController - Task tracking
12. ⏳ UserClientsController - User-client assignments
13. ⏳ GuestController - Public invoice/quote viewing
14. ⏳ GuestPaymentsController - Guest payments
15. ⏳ GuestInvoicesController - Guest invoice viewing

### Priority 2: System Management (13 controllers)

**Core Module (13):**
16. ⏳ SettingsController - Application settings
17. ⏳ DashboardController - Dashboard
18. ⏳ LayoutController - Layout management
19. ⏳ SetupController - Installation wizard
20. ⏳ EmailTemplatesController - Email template management
21. ⏳ CustomFieldsController - Custom fields
22. ⏳ CustomValuesController - Custom field values
23. ⏳ UploadController - File uploads
24. ⏳ MailerController - Email sending
25. ⏳ ImportController - Data import
26. ⏳ ReportsController - Report generation
27. ⏳ FilterController - Filtering utilities
28. ⏳ WelcomeController - Welcome page

### Priority 3: Supporting Features (16 controllers)

**Payments Module (3):**
29. ⏳ PaymentsController - Payment management
30. ⏳ PaymentMethodsController - Payment methods
31. ⏳ MerchantController - Payment gateway integration

**Products Module (5):**
32. ⏳ ProductsController - Product management
33. ⏳ FamiliesController - Product families
34. ⏳ UnitsController - Units of measure
35. ⏳ TaxRatesController - Tax rates
36. ⏳ ProductsAjaxController - Ajax operations

**Users Module (3):**
37. ⏳ UsersController - User management
38. ⏳ SessionsController - Login/logout
39. ⏳ UserClientsController - User assignments

**Guest Module (5):**
40. ⏳ GuestQuotesController - Guest quote viewing
41. ⏳ GuestPaypalController - PayPal integration
42. ⏳ GuestStripeController - Stripe integration
43. ⏳ GuestPaymentInformationController - Payment info
44. ⏳ GuestViewController - Generic guest views

---

## Test Coverage Requirements

For each controller method, create tests that verify:

### 1. Happy Path
- Valid input produces expected output
- Data is correctly saved/retrieved
- Correct view/redirect is returned

### 2. Authentication/Authorization
- Unauthenticated users are redirected
- Unauthorized users receive 403
- Correct permissions are checked

### 3. Validation
- Required fields are validated
- Data types are validated
- Business rules are enforced

### 4. Edge Cases
- Empty results sets
- Non-existent resources (404)
- Duplicate data handling
- Concurrent modifications

### 5. Data Integrity
- Related records are handled correctly
- Cascading deletes work properly
- Calculations are accurate
- Totals match expected values

### Example Test File Structure:

```php
<?php

namespace Tests\Feature\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use Modules\Quotes\Http\Controllers\QuotesController;
use Modules\Quotes\Entities\Quote;

/**
 * QuotesController Feature Tests
 * 
 * Comprehensive test coverage for all QuotesController methods
 * Tests authentication, authorization, validation, and business logic
 */
#[CoversClass(QuotesController::class)]
class QuotesControllerTest extends TestCase
{
    /**
     * Test that index redirects to status view
     */
    #[Test]
    public function it_redirects_to_all_status_view_from_index(): void
    {
        // Arrange
        // (No specific arrangement needed)
        
        // Act
        $response = $this->get('/quotes');
        
        // Assert
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/quotes/status/all', $response->getHeaderLine('Location'));
    }
    
    /**
     * Test filtering quotes by draft status
     */
    #[Test]
    public function it_displays_only_draft_quotes_when_draft_status_selected(): void
    {
        // Arrange
        $draftQuote = Quote::factory()->draft()->create();
        $sentQuote = Quote::factory()->sent()->create();
        
        // Act
        $response = $this->get('/quotes/status/draft');
        
        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $viewData = $response->getViewData();
        $this->assertCount(1, $viewData['quotes']);
        $this->assertEquals($draftQuote->quote_id, $viewData['quotes'][0]->quote_id);
    }
    
    /**
     * Test quote creation with valid data
     */
    #[Test]
    public function it_creates_quote_with_valid_client_and_items_data(): void
    {
        // Arrange
        $quoteData = [
            'client_id' => 1,
            'quote_date_created' => '2025-01-15',
            'quote_date_expires' => '2025-02-15',
            'items' => [
                ['item_name' => 'Service 1', 'item_quantity' => 1, 'item_price' => 100.00],
                ['item_name' => 'Service 2', 'item_quantity' => 2, 'item_price' => 50.00],
            ]
        ];
        
        // Act
        $response = $this->post('/quotes', $quoteData);
        
        // Assert
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('ip_quotes', [
            'client_id' => 1,
            'quote_date_created' => '2025-01-15',
        ]);
        $this->assertDatabaseCount('ip_quote_items', 2);
        
        // Verify calculations
        $quote = Quote::latest()->first();
        $this->assertEquals(200.00, $quote->amounts->quote_total);
    }
    
    /**
     * Test quote view returns 404 for non-existent quote
     */
    #[Test]
    public function it_returns_404_when_viewing_non_existent_quote(): void
    {
        // Arrange
        $nonExistentId = 99999;
        
        // Act
        $response = $this->get('/quotes/view/' . $nonExistentId);
        
        // Assert
        $this->assertEquals(404, $response->getStatusCode());
    }
    
    /**
     * Test quote deletion removes quote and related records
     */
    #[Test]
    public function it_deletes_quote_and_all_related_records_when_deleting(): void
    {
        // Arrange
        $quote = Quote::factory()
            ->has(QuoteItem::factory()->count(3))
            ->has(QuoteTaxRate::factory()->count(1))
            ->create();
        
        // Act
        $response = $this->delete('/quotes/' . $quote->quote_id);
        
        // Assert
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseMissing('ip_quotes', ['quote_id' => $quote->quote_id]);
        $this->assertDatabaseCount('ip_quote_items', 0);
        $this->assertDatabaseCount('ip_quote_tax_rates', 0);
    }
    
    /**
     * Test PDF generation with valid quote
     */
    #[Test]
    public function it_generates_pdf_with_correct_content_for_valid_quote(): void
    {
        // Arrange
        $quote = Quote::factory()
            ->has(QuoteItem::factory()->count(2))
            ->create(['quote_number' => 'QT-2025-001']);
        
        // Act
        $response = $this->get('/quotes/generate_pdf/' . $quote->quote_id);
        
        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('QT-2025-001', $response->getContent());
    }
    
    /**
     * Test validation fails with missing required fields
     */
    #[Test]
    public function it_returns_validation_errors_when_required_fields_missing(): void
    {
        // Arrange
        $invalidData = [
            'quote_date_created' => '2025-01-15',
            // Missing client_id (required)
        ];
        
        // Act
        $response = $this->post('/quotes', $invalidData);
        
        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        $errors = $response->getJsonData()['errors'];
        $this->assertArrayHasKey('client_id', $errors);
    }
}
```

---

## Implementation Timeline

**Completed:**
- ✅ Phase 1: PSR-4 Naming
- ✅ Phase 2: Model Migration (38+ models)
- ✅ Phase 3 Setup: Testing infrastructure

**Remaining:**
- ⏳ Phase 3: Controller Migration (44 controllers)
  - Estimated: 40-60 hours
  - Pattern: 1-2 hours per controller (migration + tests)
- ⏳ Phase 4: View verification (already migrated)
- ⏳ Phase 5: Unmapped modules
- ⏳ Phase 6: Verification & cleanup
- ⏳ Phase 7: Linters & code quality
- ⏳ Phase 8: Documentation

---

## Next Steps

1. **Start with Priority 1 controllers** (Quotes, Invoices, CRM)
2. **Follow the established pattern** for each controller
3. **Write tests first** (TDD approach recommended)
4. **Verify calculations** especially for financial operations
5. **Update routes** to point to new controllers
6. **Gradual cutover** - test new controllers alongside legacy

---

## Success Criteria

For Phase 3 to be considered complete:

- [ ] All 44 controllers migrated to PSR-4
- [ ] Every controller method has PHPDoc with legacy references
- [ ] Comprehensive tests for all controller methods
- [ ] All tests passing
- [ ] Test coverage > 80%
- [ ] No syntax errors
- [ ] PSR-12 compliant
- [ ] All routes updated
- [ ] Documentation updated

---

**Status:** Infrastructure ready, implementation pattern defined, ready to begin controller migration.
