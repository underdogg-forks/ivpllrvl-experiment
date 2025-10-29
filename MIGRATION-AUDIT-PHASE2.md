# Migration Audit Report - Phase 2 Model Migrations

**Date:** 2025-10-29
**Phase:** 2 - Complete Model Migrations
**Status:** IN PROGRESS (2 of 40+ models completed)

## Executive Summary

Phase 1 (PSR-4 Naming Violations) has been successfully completed. All entity and controller files now follow PSR-4 naming conventions with no underscores in class names.

Phase 2 has begun with a focus on the most critical models for business calculations. Two critical models have been fully migrated:

1. **Quote.php** - Complete (30/30 methods)
2. **QuoteAmount.php** - Complete (7/7 methods)

These represent the foundation for quote functionality, including all business logic for quote creation, status management, and financial calculations.

## Completed Models (2/40+)

### 1. Quote Model (Modules/Quotes/Entities/Quote.php) ✅

**Source:** `application/modules/quotes/models/Mdl_quotes.php`
**Methods Migrated:** 30/30 (100%)

#### Methods Implemented:

**Static Methods:**
- `statuses()` - Quote status definitions array
- `validationRules()` - Validation rules for creating quotes
- `validationRulesSaveQuote()` - Validation rules for saving quotes
- `createQuote()` - Create quote with associated amounts and tax rates
- `copyQuote()` - Copy quote with all items, tax rates, and custom fields
- `getDateDue()` - Calculate quote expiry date based on settings
- `getQuoteNumber()` - Generate next quote number from invoice group
- `getUrlKey()` - Generate unique URL key for guest access
- `getInvoiceGroupId()` - Get invoice group ID for a quote
- `deleteQuote()` - Delete quote and cleanup all orphaned records
- `approveQuoteByKey()` - Approve quote via URL key (guest access)
- `rejectQuoteByKey()` - Reject quote via URL key (guest access)
- `approveQuoteById()` - Approve quote by ID
- `rejectQuoteById()` - Reject quote by ID
- `markViewed()` - Mark quote as viewed (only if sent)
- `markSent()` - Mark quote as sent (only if draft)
- `generateQuoteNumberIfApplicable()` - Generate number based on settings

**Query Scopes:**
- `scopeByStatus()` - Filter by status ID
- `scopeDraft()` - Filter draft quotes (status 1)
- `scopeSent()` - Filter sent quotes (status 2)
- `scopeViewed()` - Filter viewed quotes (status 3)
- `scopeApproved()` - Filter approved quotes (status 4)
- `scopeRejected()` - Filter rejected quotes (status 5)
- `scopeCanceled()` - Filter canceled quotes (status 6)
- `scopeOpen()` - Filter open quotes (sent or viewed - for guest module)
- `scopeGuestVisible()` - Filter guest-visible quotes (sent, viewed, approved, rejected)
- `scopeByClient()` - Filter quotes by client ID

**Relationships:**
- `client()` - BelongsTo relationship with Client
- `user()` - BelongsTo relationship with User
- `invoiceGroup()` - BelongsTo relationship with InvoiceGroup
- `amounts()` - HasOne relationship with QuoteAmount
- `items()` - HasMany relationship with QuoteItem
- `taxRates()` - HasMany relationship with QuoteTaxRate

**Business Logic Preserved:**
- Quote creation with automatic amount record creation
- Default tax rate application based on settings
- Complex quote copying with discount calculations
- Status transition logic with validation
- Number generation based on draft/sent status and settings
- Unique URL key generation for guest access

### 2. QuoteAmount Model (Modules/Quotes/Entities/QuoteAmount.php) ✅

**Source:** `application/modules/quotes/models/Mdl_quote_amounts.php`
**Methods Migrated:** 7/7 (100%)

#### Methods Implemented:

**Calculation Methods (CRITICAL FOR ACCURACY):**
- `calculate()` - Master calculation method for quote totals
  - Aggregates item subtotals and tax totals
  - Applies global discounts (new calculation mode)
  - Applies percentage and amount discounts (legacy mode)
  - Handles both legacy and new calculation modes
  - Updates or creates quote amount record
  - Triggers tax calculation
  
- `calculateDiscount()` - Calculate discount for legacy mode
  - Applies fixed discount amount
  - Applies percentage discount
  - Proper decimal rounding based on settings
  
- `getGlobalDiscount()` - Get global discount for new calculation mode
  - Calculates discount distributed across items
  - Required for accurate quote total calculation
  
- `calculateQuoteTaxes()` - Calculate and apply quote-level taxes
  - Loops through all applied tax rates
  - Handles "include item tax" flag
  - Updates quote tax rate amounts
  - Recalculates quote total with taxes
  - Applies discounts in legacy mode after taxes
  
**Reporting Methods:**
- `getTotalQuoted()` - Get total quoted for period
  - Supports: all time, month, last_month, year, last_year
  - Used for dashboard statistics
  
- `getStatusTotals()` - Get quote totals by status for period
  - Supports: this-month, last-month, this-quarter, last-quarter, this-year, last-year
  - Returns sum and count by status
  - Merges with status definitions from Quote model
  - Used for dashboard and reports

**Calculation Modes Supported:**
1. **Legacy Mode** (`legacy_calculation = true`):
   - Discounts applied after item totals
   - Quote-level taxes calculated on subtotal + item taxes
   - Discounts then applied to final total
   
2. **New Mode** (`legacy_calculation = false`):
   - Global discount distributed across items
   - Item calculations include proportional discount
   - Quote total = item subtotal + item taxes (no quote-level taxes)

**Relationships:**
- `quote()` - BelongsTo relationship with Quote

## Partially Complete Models

### 3. QuoteItem (Modules/Quotes/Entities/QuoteItem.php) ⚠️

**Source:** `application/modules/quotes/models/Mdl_quote_items.php`
**Methods Migrated:** 5/7 (71%)

**Missing Methods:**
- `save()` - Override save to trigger amount calculations
- `delete()` - Override delete to recalculate quote amounts
- `getItemsSubtotal()` - Get items subtotal for discount calculations

**Impact:** HIGH - These methods are required for proper quote calculations

### 4. QuoteTaxRate (Modules/Quotes/Entities/QuoteTaxRate.php) ⚠️

**Source:** `application/modules/quotes/models/Mdl_quote_tax_rates.php`
**Methods Migrated:** 2/4 (50%)

**Missing Methods:**
- `save()` - Override save to trigger tax calculations
- `validationRules()` - Validation rules for tax rates

**Impact:** MEDIUM - Required for managing quote-level taxes

### 5. QuoteItemAmount (Modules/Quotes/Entities/QuoteItemAmount.php) ⚠️

**Source:** `application/modules/quotes/models/Mdl_quote_item_amounts.php`
**Methods Migrated:** Unknown (needs verification)

**Missing Methods:** Need to analyze source file

**Impact:** HIGH - Required for item-level calculations

## Not Yet Migrated Models (35+ remaining)

### Invoices Module (9 models) - CRITICAL PRIORITY

1. **Invoice.php** ⚠️
   - Source: `Mdl_invoices.php` (32+ methods)
   - Current: 15 methods
   - Missing: ~17 methods
   - Impact: CRITICAL - Core business model

2. **InvoiceAmount.php** ⚠️
   - Source: `Mdl_invoice_amounts.php` (9 methods)
   - Current: Unknown
   - Impact: CRITICAL - Invoice calculations

3. **Item.php** ⚠️
   - Source: `Mdl_items.php` (7 methods)
   - Current: Unknown
   - Impact: CRITICAL - Invoice line items

4. **InvoiceTaxRate.php** ⚠️
   - Source: `Mdl_invoice_tax_rates.php` (4 methods)
   - Current: Unknown
   - Impact: CRITICAL - Tax calculations

5. **ItemAmount.php** ⚠️
   - Source: `Mdl_item_amounts.php` (1 method)
   - Current: Unknown
   - Impact: HIGH

6. **InvoiceSumex.php** ⚠️
   - Source: `Mdl_invoice_sumex.php` (3 methods)
   - Current: 1 method
   - Missing: 2 methods
   - Impact: MEDIUM (Swiss medical billing)

7. **InvoicesRecurring.php** ⚠️
   - Source: `Mdl_invoices_recurring.php` (8 methods)
   - Current: 1 method
   - Missing: 7 methods
   - Impact: HIGH - Recurring invoices

8. **Template.php** ⚠️
   - Source: `Mdl_templates.php` (3 methods)
   - Current: Unknown
   - Impact: MEDIUM

9. **InvoiceGroup.php** ⚠️
   - Source: `Mdl_invoice_groups.php` (6 methods)
   - Current: Unknown
   - Impact: HIGH - Number generation

### Products Module (4 models) - HIGH PRIORITY

1. **Product.php** ❌
   - Source: `Mdl_products.php` (7 methods)
   - Current: Not migrated
   - Impact: HIGH

2. **Family.php** ❌
   - Source: `Mdl_families.php` (3 methods)
   - Current: Not migrated
   - Impact: MEDIUM

3. **TaxRate.php** ❌
   - Source: `Mdl_tax_rates.php` (3 methods)
   - Current: Not migrated
   - Impact: HIGH - Used in calculations

4. **Unit.php** ❌
   - Source: `Mdl_units.php` (4 methods)
   - Current: Not migrated
   - Impact: MEDIUM

### Payments Module (3 models) - HIGH PRIORITY

1. **Payment.php** ❌
   - Source: `Mdl_payments.php` (10 methods)
   - Current: Not migrated
   - Impact: CRITICAL

2. **PaymentLog.php** ❌
   - Source: `Mdl_payment_logs.php` (3 methods)
   - Current: Not migrated
   - Impact: MEDIUM

3. **PaymentMethod.php** ❌
   - Source: `Mdl_payment_methods.php` (3 methods)
   - Current: Not migrated
   - Impact: HIGH

### CRM Module (5 models) - HIGH PRIORITY

1. **Client.php** ❌
   - Source: `Mdl_clients.php` (15 methods)
   - Current: Not migrated
   - Impact: CRITICAL

2. **ClientNote.php** ❌
   - Source: `Mdl_client_notes.php` (4 methods)
   - Current: Not migrated
   - Impact: MEDIUM

3. **Project.php** ❌
   - Source: `Mdl_projects.php` (6 methods)
   - Current: Not migrated
   - Impact: MEDIUM

4. **Task.php** ❌
   - Source: `Mdl_tasks.php` (14 methods)
   - Current: Not migrated
   - Impact: MEDIUM

5. **UserClient.php** ⚠️
   - Source: `Mdl_user_clients.php` (7 methods)
   - Current: Unknown
   - Impact: MEDIUM

### Users Module (3 models) - MEDIUM PRIORITY

1. **User.php** ❌
   - Source: `Mdl_users.php` (11 methods)
   - Current: Not migrated
   - Impact: HIGH

2. **Session.php** ❌
   - Source: `Mdl_sessions.php` (1 method)
   - Current: Not migrated
   - Impact: MEDIUM

3. **UserClient.php** - See CRM Module

### Custom Module (7 models) - MEDIUM PRIORITY

1. **CustomField.php** ⚠️
   - Source: `Mdl_custom_fields.php` (17 methods)
   - Current: Unknown
   - Impact: MEDIUM

2. **CustomValue.php** ⚠️
   - Source: `Mdl_custom_values.php` (16 methods)
   - Current: Unknown
   - Impact: MEDIUM

3. **ClientCustom.php** ⚠️
   - Source: `Mdl_client_custom.php` (9 methods)
   - Current: Unknown
   - Impact: MEDIUM

4. **InvoiceCustom.php** ⚠️
   - Source: `Mdl_invoice_custom.php` (5 methods)
   - Current: Unknown
   - Impact: MEDIUM

5. **QuoteCustom.php** ⚠️
   - Source: `Mdl_quote_custom.php` (5 methods)
   - Current: Unknown
   - Impact: MEDIUM

6. **PaymentCustom.php** ⚠️
   - Source: `Mdl_payment_custom.php` (6 methods)
   - Current: Unknown
   - Impact: MEDIUM

7. **UserCustom.php** ❌
   - Source: `Mdl_user_custom.php` (6 methods)
   - Current: Not migrated
   - Impact: LOW

### Core Module (3+ models) - MEDIUM PRIORITY

1. **Setting.php** ⚠️
   - Source: `Mdl_settings.php` (8 methods)
   - Current: Unknown
   - Impact: HIGH

2. **Version.php** ⚠️
   - Source: `Mdl_versions.php` (3 methods)
   - Current: Unknown
   - Impact: LOW

3. **Setup.php** ⚠️
   - Source: `Mdl_setup.php` (12 methods)
   - Current: Unknown
   - Impact: MEDIUM

## Migration Methodology

The migration follows these principles:

1. **One-to-One Method Migration**: Every method from legacy code is migrated
2. **Business Logic Preservation**: All calculations, validations, and business rules are preserved
3. **Eloquent Conversion**: CodeIgniter Query Builder → Eloquent ORM
4. **Type Safety**: Add parameter and return type hints
5. **PSR-12 Compliance**: Follow modern PHP coding standards
6. **Static Methods**: Use static methods for utility functions and complex operations

### Conversion Patterns

**CodeIgniter → Eloquent:**
```php
// OLD
$this->db->where('quote_id', $id);
$this->db->update('ip_quotes', ['status' => 2]);

// NEW
Quote::where('quote_id', $id)->update(['status' => 2]);
```

**Model Loading → Direct Use:**
```php
// OLD
$this->load->model('quotes/mdl_quotes');
$quote = $this->mdl_quotes->get_by_id($id);

// NEW
$quote = Quote::findOrFail($id);
```

**Query Results:**
```php
// OLD
$result = $this->db->get('ip_quotes')->row();

// NEW
$result = Quote::first();
```

## Recommended Next Steps

### Priority 1: Complete Quotes Module
1. Finish QuoteItem.php (2 methods remaining)
2. Finish QuoteTaxRate.php (2 methods remaining)
3. Verify QuoteItemAmount.php is complete

### Priority 2: Invoice Module (Critical for Business)
1. Complete Invoice.php (~17 methods)
2. Complete InvoiceAmount.php (9 methods) - CRITICAL
3. Complete Item.php (7 methods)
4. Complete InvoiceTaxRate.php (4 methods)
5. Complete ItemAmount.php (1 method)
6. Complete remaining invoice models

### Priority 3: Core Business Entities
1. Complete Client.php (15 methods) - CRITICAL
2. Complete Payment.php (10 methods) - CRITICAL
3. Complete Product.php (7 methods)
4. Complete User.php (11 methods)
5. Complete TaxRate.php (3 methods)

### Priority 4: Supporting Models
1. Complete all Custom field models
2. Complete remaining CRM models (Project, Task, ClientNote)
3. Complete PaymentMethod, PaymentLog
4. Complete Product supporting models (Family, Unit)

### Priority 5: System Models
1. Complete Setting.php
2. Complete Setup.php
3. Complete Version.php
4. Complete Session.php

## Testing Checklist

For each migrated model, verify:

- [ ] All methods from legacy code are present
- [ ] Method signatures include type hints
- [ ] Business logic is preserved
- [ ] Calculations are accurate (especially for amounts/taxes)
- [ ] Relationships are defined correctly
- [ ] Query scopes work as expected
- [ ] No syntax errors (`php -l`)
- [ ] Compatible with existing controllers/views

## Known Issues / Considerations

1. **Configuration Functions**: Some methods use `config_item()` and `get_setting()` which need to work with both old and new systems
2. **Database Access**: Some methods use raw DB queries via `\DB::` facade - ensure Illuminate Database is properly configured
3. **Legacy Calculation Mode**: Both calculation modes (legacy and new) must be supported for backwards compatibility
4. **Custom Fields**: Complex interaction between multiple custom field models needs careful migration
5. **Orphan Cleanup**: Several delete methods reference `delete_orphans()` helper - ensure this is available

## Estimates

**Remaining Work:**
- Models to complete: ~38
- Average methods per model: ~7
- Total methods to migrate: ~266
- Estimated time per method: 5-10 minutes
- Total estimated time: 22-44 hours

**Breakdown by Priority:**
- Priority 1 (Quotes): 1-2 hours
- Priority 2 (Invoices): 8-12 hours
- Priority 3 (Core Entities): 6-10 hours
- Priority 4 (Supporting): 4-8 hours
- Priority 5 (System): 3-6 hours
- Testing & Fixes: 6-10 hours

## Conclusion

Phase 2 migration is well underway with the foundation laid for quote functionality. The approach is solid and the migrated code follows best practices. However, significant work remains to complete all models.

The critical path forward is:
1. Complete Quote module (highest immediate value)
2. Migrate Invoice module (critical for business)
3. Migrate core entities (Client, Payment, Product, User)
4. Systematic completion of remaining models
5. Comprehensive testing of calculations

**Status:** 2 of 40+ models complete (5%)
**Next Milestone:** Complete Quotes module (5 models)
**Critical Blocker:** None - work can proceed incrementally
