# CodeIgniter Removal - Remaining Work

## Summary
This document tracks the remaining CodeIgniter artifacts that need to be removed from the codebase.

## Current Status

### Completed ✅
- **5 Support/Helper classes**: ClientHelper, CustomValuesHelper, EchoHelper, OrphanHelper, EInvoiceHelper (partial)
- **3 Library classes**: QrCode, Zugferdv10Xml, Sumex (constructor only)
- **5 View files**: Fixed `$this->db->escape_str()` and `get_instance()` patterns
- **All `$this->db->` patterns removed**: 0 occurrences remaining

### Metrics
- **$CI->** patterns: 62 remaining (down from 85)
- **get_instance()** calls: 9 remaining (down from 15)
- **$this->db->** patterns: 0 remaining (down from 5) ✅
- **mdl_** references: ~120 remaining (mostly in views)

## Remaining Files (8 Critical Files)

### 1. Modules/Core/Support/PdfHelper.php
**Priority: CRITICAL**
**Complexity: HIGH**
**Occurrences: ~32 $CI-> patterns**

**Issues:**
- `$CI->load->model()` - Loading Invoice, Item, CustomField, PaymentMethod models
- `$CI->load->helper()` - Loading country, client, template, e-invoice, mpdf helpers
- `$CI->load->view()` - View rendering for PDF templates
- `$CI->mdl_invoices->get_by_id()` - Invoice retrieval
- `$CI->mdl_items->where()` - Item queries
- `$CI->mdl_custom_fields->get_values_for_fields()` - Custom field retrieval
- `$CI->mdl_invoice_tax_rates->where()` - Tax rate queries

**Required Refactoring:**
```php
// Current pattern:
$CI = get_instance();
$CI->load->model(['invoices/mdl_invoices']);
$invoice = $CI->mdl_invoices->get_by_id($invoice_id);

// Should become:
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;

$invoice = Invoice::with(['client', 'items', 'taxRates'])->find($invoice_id);
// OR via service:
$invoiceService = app(InvoiceService::class);
$invoice = $invoiceService->findOrFail($invoice_id);
```

**Methods to Fix:**
- `generate_invoice_pdf()` - Main invoice PDF generation
- `generate_invoice_sumex()` - Sumex invoice PDF
- `generate_quote_pdf()` - Quote PDF generation

### 2. Modules/Core/Libraries/Sumex.php
**Priority: HIGH**
**Complexity: HIGH**
**Occurrences: ~12 $CI-> patterns in pdf() method**

**Issues:**
- Constructor already fixed ✅
- `pdf()` method still uses CI:
  - `$CI->load->model()` - Loading models
  - `$CI->load->helper()` - Loading template, pdf, mpdf helpers
  - `$CI->load->view()` - View rendering
  - `$CI->mdl_invoice_tax_rates->where()` - Tax queries
  - `$CI->mdl_custom_fields->get_values_for_fields()` - Custom fields

**Required Refactoring:**
- Convert pdf() method to use services
- Replace view loading with Laravel view rendering
- Use Eloquent for all database queries

### 3. Modules/Core/Helpers/mailer_helper.php
**Priority: HIGH**
**Complexity: MEDIUM**
**Occurrences: ~4 $CI-> patterns**

**Issues:**
- `$CI->load->helper()` - Helper loading
- `$CI->mdl_invoices->where()` - Invoice queries
- `$CI->mdl_quotes->where()` - Quote queries

**Required Refactoring:**
- Replace with Laravel Mail/Notification system
- Use Eloquent models directly
- Remove helper dependencies

### 4. Modules/Core/Support/TemplateHelper.php
**Priority: HIGH**
**Complexity: MEDIUM**
**Occurrences: ~8 $CI-> patterns**

**Issues:**
- `$CI->mdl_custom_fields->get_by_id()` - Custom field retrieval
- `$CI->mdl_custom_fields->get_value_for_field()` - Custom field values
- `$CI->cv->get_by_id()` - Custom value retrieval
- `$CI->mdl_invoices->statuses()` - Invoice status lookup

**Required Refactoring:**
```php
// Current:
$cf = $CI->mdl_custom_fields->get_by_id($cf_id[1]);

// Should become:
use Modules\Core\Models\CustomField;
$cf = CustomField::find($cf_id[1]);
```

### 5. Modules/Core/Support/InvoiceHelper.php
**Priority: MEDIUM**
**Complexity: MEDIUM**
**Occurrences: ~3 $CI-> patterns**

**Issues:**
- `$CI->mdl_invoices->get_by_id()` - Invoice retrieval
- `$CI->qrcode->generate()` - QR code generation

**Required Refactoring:**
- Use Invoice model directly
- Use QrCode library directly (already fixed in library itself)

### 6. Modules/Core/Support/PagerHelper.php
**Priority: MEDIUM**
**Complexity: MEDIUM**
**Occurrences: ~5 $CI-> patterns**

**Issues:**
- `$CI->{$model}->previous_offset` - Pagination state
- `$CI->{$model}->next_offset` - Pagination state
- `$CI->{$model}->last_offset` - Pagination state

**Required Refactoring:**
- Replace with Laravel pagination
- Update views to use paginator object
- Remove pager() helper calls from views

### 7. Modules/Core/Support/RedirectHelper.php
**Priority: LOW**
**Complexity: LOW**
**Occurrences: ~2 $CI-> patterns**

**Issues:**
- `$CI->uri->uri_string()` - Current URI
- Session management via $bridge

**Required Refactoring:**
```php
// Current:
$bridge->session()->set_userdata('redirect_to', $CI->uri->uri_string());

// Should become:
session(['redirect_to' => request()->path()]);
```

### 8. Modules/Core/Support/EInvoiceHelper.php
**Priority: MEDIUM**
**Complexity: MEDIUM**
**Occurrences: ~3 $CI-> patterns**

**Issues:**
- `$CI->load->library()` - XML library loading
- `$CI->ublciixml->xml()` - XML generation

**Required Refactoring:**
- Use dependency injection for XML libraries
- Instantiate libraries directly

## View Files (40+ files)

### Pattern: $this->mdl_*->form_value()
**Occurrences: 40-45 in various view files**

**Affected Modules:**
- Core (email templates, users, import)
- Products (product forms)
- Invoices (invoice forms)
- Payments (payment forms)
- Crm (client forms)

**Example Issue:**
```php
// Current in view:
value="<?php echo $this->mdl_products->form_value('product_name', true); ?>"

// Should receive from controller:
value="<?php echo old('product_name', $product->product_name ?? ''); ?>"
```

**Required Refactoring:**
1. Update controllers to pass model data to views
2. Use Laravel's `old()` helper for form repopulation
3. Pass actual model objects instead of relying on form_value()

### Pattern: pager() helper
**Occurrences: 10-15 view files**

**Affected Views:**
- Core/resources/views/versions.php
- Core/resources/views/index.php
- Core/resources/views/users/index.php
- Crm/resources/views/payments_index.php
- Crm/resources/views/quotes_index.php
- Invoices/resources/views/index.php
- Products/resources/views/index.php

**Required Refactoring:**
1. Implement Laravel pagination in controllers
2. Replace pager() with Laravel pagination links
3. Update views to use `$items->links()` instead

## Recommended Approach

### Phase 1: Quick Wins (Remaining Simple Fixes)
1. ✅ RedirectHelper.php - Replace with Laravel request/session
2. ✅ InvoiceHelper.php - Use models directly
3. ✅ EInvoiceHelper.php - Direct library instantiation

### Phase 2: Medium Complexity
1. TemplateHelper.php - Replace model calls with Eloquent
2. PagerHelper.php - Implement Laravel pagination
3. mailer_helper.php - Use Laravel Mail

### Phase 3: Complex Refactoring
1. PdfHelper.php - Major refactoring with service injection
2. Sumex.php pdf() method - Service-based approach
3. View files - Controller updates for proper data binding

## Helper Functions/Utilities Needed

### Settings Helper
Already available: `Setting::getValue($key)`

### Custom Fields Helper
Create a CustomFieldService for:
- `get_values_for_fields($table, $id)`
- `get_value_for_field($field_id, $table, $object)`

### Invoice/Quote Retrieval
Already available via Eloquent:
```php
Invoice::with(['client', 'items', 'taxRates'])->find($id)
Quote::with(['client', 'items', 'taxRates'])->find($id)
```

### View Rendering
Replace:
```php
$CI->load->view('template', $data, true)
```
With:
```php
view('template', $data)->render()
```

## Testing Strategy

After each fix:
1. Verify no syntax errors
2. Check that affected features still work
3. Test with actual data if possible
4. Run any existing automated tests

## Success Criteria

✅ **COMPLETE** when:
- All `$CI->` patterns removed
- All `get_instance()` calls removed
- All `$this->db->` patterns removed (DONE)
- All `mdl_*` references removed
- All views use proper data binding
- All pagination uses Laravel paginator
- No CodeIgniter dependencies remain

## Notes

- Many of these files are interconnected (PdfHelper uses TemplateHelper, etc.)
- View changes require controller updates
- Some functionality may need to be moved to Services
- Consider creating facade/helper classes for commonly used patterns
