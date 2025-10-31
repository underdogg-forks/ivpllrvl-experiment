# Quick Test Refactoring Reference Guide

## Pattern: Controller Method → HTTP Route

### Before (❌ Don't do this)
```php
class InvoicesControllerTest extends TestCase
{
    private InvoicesController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->controller = new InvoicesController();
    }

    public function test_view_invoice(): void
    {
        $invoice = Invoice::factory()->create();
        $response = $this->controller->view($invoice->invoice_id);
        $viewData = $response->getData();
        $this->assertArrayHasKey('invoice', $viewData);
    }
}
```

### After (✅ Do this)
```php
class InvoicesControllerTest extends FeatureTestCase
{
    public function it_displays_invoice_details(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(
            route('invoices.view', ['invoiceId' => $invoice->invoice_id])
        );

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('invoice');
        $viewInvoice = $response->viewData('invoice');
        $this->assertEquals($invoice->invoice_id, $viewInvoice->invoice_id);
    }
}
```

## Pattern: AJAX POST with JSON Payload

### With PHPDoc Documentation (✅ Required)
```php
/**
 * Test saving invoice with items.
 *
 * JSON Payload:
 * {
 *   "invoice_id": 1,
 *   "items": "[{\"item_name\":\"Test\",\"item_quantity\":2,\"item_price\":100.00}]",
 *   "invoice_number": "INV-001",
 *   "invoice_date_created": "2024-01-01",
 *   "invoice_status_id": 1
 * }
 */
#[Test]
public function it_saves_invoice_with_items(): void
{
    /** Arrange */
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create();
    
    $payload = [
        'invoice_id' => $invoice->invoice_id,
        'items' => json_encode([
            ['item_name' => 'Test', 'item_quantity' => 2, 'item_price' => 100.00]
        ]),
        'invoice_number' => 'INV-001',
        'invoice_date_created' => date('Y-m-d'),
        'invoice_status_id' => 1,
    ];

    /** Act */
    $response = $this->actingAs($user)->post(route('invoices.ajax.save'), $payload);

    /** Assert */
    $response->assertOk();
    $data = $response->json();
    $this->assertEquals(1, $data['success']);
    $this->assertEquals(1, Item::where('invoice_id', $invoice->invoice_id)->count());
}
```

## Common Assertions

### GET Requests
```php
// Success
$response->assertOk();
$response->assertViewIs('invoices::index');
$response->assertViewHas('invoices');

// Not Found
$response->assertNotFound();

// Redirect
$response->assertRedirect(route('invoices.index'));

// Session
$response->assertSessionHas('alert_success');
$response->assertSessionHas('alert_error');
```

### POST Requests (JSON)
```php
// Success response
$response->assertOk();
$data = $response->json();
$this->assertEquals(1, $data['success']);
$this->assertArrayHasKey('invoice_id', $data);

// Error response
$response->assertOk();
$data = $response->json();
$this->assertEquals(0, $data['success']);
$this->assertArrayHasKey('validation_errors', $data);
```

### Data Integrity
```php
// Database assertions
$this->assertDatabaseHas('ip_invoices', [
    'invoice_id' => $invoice->invoice_id,
    'invoice_number' => 'INV-001',
]);

// Count assertions
$this->assertEquals(2, Item::where('invoice_id', $invoice->invoice_id)->count());

// Null checks
$this->assertNull(Invoice::find($deletedId));
$this->assertNotNull(Invoice::find($validId));

// Model refresh and check
$invoice->refresh();
$this->assertEquals('INV-002', $invoice->invoice_number);
```

## Route Naming Convention

### Standard Routes
- `invoices.index` → GET /invoices
- `invoices.view` → GET /invoices/view/{invoiceId}
- `invoices.delete` → POST /invoices/delete/{invoiceId}

### AJAX Routes
- `invoices.ajax.save` → POST /invoices/ajax/save
- `invoices.ajax.create` → POST /invoices/ajax/create
- `invoices.ajax.delete_item` → POST /invoices/ajax/delete-item/{invoiceId}

### Modal Routes
- `invoices.modal.create` → GET /invoices/modal/create
- `invoices.modal.copy` → GET /invoices/modal/copy

## Testing Checklist

When writing a new test:
- [ ] Extends `FeatureTestCase` (not `TestCase`)
- [ ] Uses `$this->actingAs($user)` for authentication
- [ ] Calls HTTP routes (not controller methods)
- [ ] Follows Arrange-Act-Assert pattern
- [ ] Has PHPDoc block if POSTing JSON
- [ ] Asserts data integrity (not just status codes)
- [ ] Tests edge cases (validation, 404, etc.)
- [ ] Uses descriptive test name (it_does_something_when_condition)

## Available Routes

See `Modules/Invoices/routes/web/invoices.php` and `Modules/Quotes/routes/web/quotes.php` for complete route listings.

## Examples

See:
- `tests/Feature/Controllers/InvoicesControllerTest.php` - GET routes
- `tests/Feature/Controllers/InvoicesAjaxControllerTest.php` - POST JSON routes  
- `tests/Feature/Controllers/QuotesControllerTest.php` - Mixed routes
- `tests/Feature/Controllers/ProjectsControllerTest.php` - Form submissions
