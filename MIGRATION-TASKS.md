# Migration Tasks - Complete CodeIgniter to Laravel/Illuminate Migration

## Overview

This document provides a comprehensive task list for completing the one-to-one migration from CodeIgniter 3 (in `application/modules/`) to Laravel/Illuminate (in `Modules/`). This is **NOT** a simplification - every method, every piece of business logic must be migrated.

## Critical Requirements

1. **One-to-One Migration**: Every method in legacy code must exist in new code
2. **PSR-4 Compliance**: NO underscores in class names (use PascalCase)
3. **PSR-12 Compliance**: Follow modern PHP coding standards
4. **No Code Loss**: All business logic, calculations, validations must be preserved
5. **Verification**: Compare method counts before removing legacy files

## Phase 1: Fix PSR-4 Naming Violations (HIGH PRIORITY)

### Entity Classes (20 files)

These files have underscores in class names and must be renamed:

1. `Modules/Quotes/Entities/Quote_amount.php` → `QuoteAmount.php`
   - Update class name from `Quote_amount` to `QuoteAmount`
   - Update all references in other files
   
2. `Modules/Quotes/Entities/Quote_item.php` → `QuoteItem.php`
   - Update class name from `Quote_item` to `QuoteItem`
   - Update all references
   
3. `Modules/Quotes/Entities/Quote_item_amount.php` → `QuoteItemAmount.php`
   - Update class name from `Quote_item_amount` to `QuoteItemAmount`
   - Update all references
   
4. `Modules/Quotes/Entities/Quote_tax_rate.php` → `QuoteTaxRate.php`
   - Update class name from `Quote_tax_rate` to `QuoteTaxRate`
   - Update all references

5. `Modules/Invoices/Entities/Invoice_amount.php` → `InvoiceAmount.php`
6. `Modules/Invoices/Entities/Invoice_group.php` → `InvoiceGroup.php`
7. `Modules/Invoices/Entities/Invoice_sumex.php` → `InvoiceSumex.php`
8. `Modules/Invoices/Entities/Invoice_tax_rate.php` → `InvoiceTaxRate.php`
9. `Modules/Invoices/Entities/Invoices_recurring.php` → `InvoicesRecurring.php`
10. `Modules/Invoices/Entities/Item_amount.php` → `ItemAmount.php`
11. `Modules/Crm/Entities/User_client.php` → `UserClient.php`
12. `Modules/Crm/Entities/Client_note.php` → `ClientNote.php`
13. `Modules/Products/Entities/Tax_rate.php` → `TaxRate.php`
14. `Modules/Core/Entities/Invoice_custom.php` → `InvoiceCustom.php`
15. `Modules/Core/Entities/Quote_custom.php` → `QuoteCustom.php`
16. `Modules/Core/Entities/Custom_value.php` → `CustomValue.php`
17. `Modules/Core/Entities/Payment_custom.php` → `PaymentCustom.php`
18. `Modules/Core/Entities/Client_custom.php` → `ClientCustom.php`
19. `Modules/Core/Entities/Custom_field.php` → `CustomField.php`
20. `Modules/Core/Entities/Email_template.php` → `EmailTemplate.php`

### Controller Classes (7 files)

1. `Modules/Crm/Http/Controllers/User_clientsController.php` → `UserClientsController.php`
2. `Modules/Crm/Http/Controllers/Payment_informationController.php` → `PaymentInformationController.php`
3. `Modules/Products/Http/Controllers/Tax_ratesController.php` → `TaxRatesController.php`
4. `Modules/Core/Http/Controllers/Custom_fieldsController.php` → `CustomFieldsController.php`
5. `Modules/Core/Http/Controllers/Custom_valuesController.php` → `CustomValuesController.php`
6. `Modules/Core/Http/Controllers/Email_templatesController.php` → `EmailTemplatesController.php`
7. `Modules/Core/Http/Controllers/Invoice_groupsController.php` → `InvoiceGroupsController.php` (if exists)

**After renaming each file:**
- Update the class name inside the file
- Search for all usages and update import statements
- Update route definitions if needed
- Run `composer dump-autoload`

## Phase 2: Complete Model Migrations (CRITICAL)

### Quotes Module

**Current State:** Quote.php has 10 methods, legacy Mdl_quotes.php has 30 methods

**Required Actions:**

1. Migrate ALL methods from `application/modules/quotes/models/Mdl_quotes.php` to `Modules/Quotes/Entities/Quote.php`:
   - `statuses()` - Convert to static method or config
   - `default_select()` - Convert to Eloquent scopes/with()
   - `default_order_by()` - Convert to Eloquent orderBy
   - `default_join()` - Convert to Eloquent with() relationships
   - `validation_rules()` - Convert to Laravel validation or model rules
   - `validation_rules_save_quote()` - Convert to Laravel validation
   - `create()` - Override Eloquent create with business logic
   - `copy_quote($source_id, $target_id)` - Full migration with discount calculation
   - `db_array()` - Convert to setAttribute/getAttribute or model events
   - `get_date_due($quote_date_created)` - Business logic method
   - `get_quote_number($invoice_group_id)` - Business logic method
   - `get_url_key()` - Business logic method
   - `get_invoice_group_id($invoice_id)` - Helper method
   - `delete($quote_id)` - Override Eloquent delete
   - `is_draft()` - Already exists as scope, verify completeness
   - `is_sent()` - Already exists as scope, verify completeness
   - `is_viewed()` - Add scope if missing
   - `is_approved()` - Already exists as scope, verify completeness
   - `is_rejected()` - Add scope if missing
   - `is_canceled()` - Add scope if missing
   - `is_open()` - Add scope if missing
   - `guest_visible()` - Add scope if missing
   - `by_client($client_id)` - Add scope if missing
   - `approve_quote_by_key($quote_url_key)` - Business logic method
   - `reject_quote_by_key($quote_url_key)` - Business logic method
   - `approve_quote_by_id($quote_id)` - Business logic method
   - `reject_quote_by_id($quote_id)` - Business logic method
   - `mark_viewed($quote_id)` - Business logic method
   - `mark_sent($quote_id)` - Business logic method
   - `generate_quote_number_if_applicable($quote_id)` - Business logic method

2. Migrate `Mdl_quote_amounts.php` (7 methods) to `QuoteAmount.php` (currently 1 method):
   - File: `application/modules/quotes/models/Mdl_quote_amounts.php`
   - Target: `Modules/Quotes/Entities/QuoteAmount.php`
   - All calculation methods must be migrated

3. Migrate `Mdl_quote_items.php` (7 methods) to `QuoteItem.php` (currently 5 methods):
   - File: `application/modules/quotes/models/Mdl_quote_items.php`
   - Target: `Modules/Quotes/Entities/QuoteItem.php`
   - Missing 2 methods

4. Migrate `Mdl_quote_tax_rates.php` (4 methods) to `QuoteTaxRate.php` (currently 2 methods):
   - File: `application/modules/quotes/models/Mdl_quote_tax_rates.php`
   - Target: `Modules/Quotes/Entities/QuoteTaxRate.php`
   - Missing 2 methods

5. Migrate `Mdl_quote_item_amounts.php` (1 method) to `QuoteItemAmount.php` (currently 1 method):
   - Verify the method is correctly migrated

### Invoices Module

**Current State:** Invoice.php has 15 methods, legacy Mdl_invoices.php has 32 methods

**Required Actions:**

1. Migrate ALL methods from `application/modules/invoices/models/Mdl_invoices.php` to `Modules/Invoices/Entities/Invoice.php`:
   - `statuses()` - 26 status definitions
   - `default_select()` - Complex SQL with calculations
   - `default_order_by()` - Sort logic
   - `default_join()` - Multiple table joins
   - `validation_rules()` - Full validation rules
   - `validation_rules_save_invoice()` - Save validation
   - `create($db_array, $include_invoice_tax_rates)` - Complex creation with tax rates
   - `copy_invoice($source_id, $target_id, $copy_recurring_items_only)` - Full copy with items, taxes, custom fields
   - `copy_credit_invoice($source_id, $target_id)` - Credit invoice specific copy
   - `db_array()` - Data preparation logic
   - `get_payments($invoice)` - Payment retrieval
   - `get_date_due($invoice_date_created)` - Due date calculation
   - `get_invoice_number($invoice_group_id)` - Number generation
   - `get_url_key()` - Unique key generation
   - `get_invoice_group_id($invoice_id)` - Helper method
   - `get_parent_invoice_number($parent_invoice_id)` - Parent invoice logic
   - `get_custom_values($id)` - Custom field values
   - `get_archives($invoice_number)` - Archive retrieval
   - `delete($invoice_id)` - Delete with orphan cleanup
   - `is_open()` - Status check scope
   - `is_sumex()` - Sumex check scope
   - `guest_visible()` - Visibility scope
   - `is_draft()` - Draft scope
   - `is_sent()` - Sent scope
   - `is_viewed()` - Viewed scope
   - `is_paid()` - Paid scope
   - `is_overdue()` - Overdue scope
   - `by_client($client_id)` - Client filter scope
   - `mark_viewed($invoice_id)` - Mark as viewed
   - `mark_sent($invoice_id)` - Mark as sent
   - `generate_invoice_number_if_applicable($invoice_id)` - Number generation
   - `mark_recurring_created($invoice_id)` - Recurring flag
   - Plus additional methods...

2. Create COMPLETE `InvoiceAmount.php` from `Mdl_invoice_amounts.php` (9 methods):
   - This model handles all invoice amount calculations
   - Critical for invoice total accuracy
   - Must include: `calculate()`, `sum_items()`, `get_item_subtotal()`, etc.

3. Create COMPLETE `Item.php` from `Mdl_items.php` (7 methods):
   - Invoice line items
   - Must handle pricing, quantities, discounts

4. Create COMPLETE `InvoiceTaxRate.php` from `Mdl_invoice_tax_rates.php` (4 methods):
   - Tax calculation logic
   - Must be accurate

5. Create COMPLETE `ItemAmount.php` from `Mdl_item_amounts.php` (1 method)

6. Create COMPLETE `InvoiceSumex.php` from `Mdl_invoice_sumex.php` (3 methods):
   - Currently only has 1 method, missing 2

7. Create COMPLETE `InvoicesRecurring.php` from `Mdl_invoices_recurring.php` (8 methods):
   - Currently only has 1 method, missing 7
   - Critical for recurring invoice functionality

8. Create COMPLETE `Template.php` from `Mdl_templates.php` (3 methods)

9. Create COMPLETE `InvoiceGroup.php` from `Mdl_invoice_groups.php` (6 methods):
   - Critical for invoice numbering

### Products Module

1. Create `Product.php` from `application/modules/products/models/Mdl_products.php` (7 methods)
2. Create `Family.php` from `application/modules/families/models/Mdl_families.php` (3 methods)
3. Create `TaxRate.php` from `application/modules/tax_rates/models/Mdl_tax_rates.php` (3 methods)
4. Create `Unit.php` from `application/modules/units/models/Mdl_units.php` (4 methods)

### Payments Module

1. Create `Payment.php` from `application/modules/payments/models/Mdl_payments.php` (10 methods)
2. Create `PaymentLog.php` from `application/modules/payments/models/Mdl_payment_logs.php` (3 methods)
3. Create `PaymentMethod.php` from `application/modules/payment_methods/models/Mdl_payment_methods.php` (3 methods)

### CRM Module

1. Create `Client.php` from `application/modules/clients/models/Mdl_clients.php` (15 methods)
2. Create `ClientNote.php` from `application/modules/clients/models/Mdl_client_notes.php` (4 methods)
3. Create `Project.php` from `application/modules/projects/models/Mdl_projects.php` (6 methods)
4. Create `Task.php` from `application/modules/tasks/models/Mdl_tasks.php` (14 methods)

### Users Module

1. Create `User.php` from `application/modules/users/models/Mdl_users.php` (11 methods)
2. Create `Session.php` from `application/modules/sessions/models/Mdl_sessions.php` (1 method)
3. Create `UserClient.php` from `application/modules/user_clients/models/Mdl_user_clients.php` (7 methods)

### Custom Module

1. Create `CustomField.php` from `application/modules/custom_fields/models/Mdl_custom_fields.php` (17 methods)
2. Create `CustomValue.php` from `application/modules/custom_values/models/Mdl_custom_values.php` (16 methods)
3. Create `ClientCustom.php` from `application/modules/custom_fields/models/Mdl_client_custom.php` (9 methods)
4. Create `InvoiceCustom.php` from `application/modules/custom_fields/models/Mdl_invoice_custom.php` (5 methods)
5. Create `QuoteCustom.php` from `application/modules/custom_fields/models/Mdl_quote_custom.php` (5 methods)
6. Create `PaymentCustom.php` from `application/modules/custom_fields/models/Mdl_payment_custom.php` (6 methods)
7. Create `UserCustom.php` from `application/modules/custom_fields/models/Mdl_user_custom.php` (6 methods)

### Core Module

1. Create `Settings.php` from `application/modules/settings/models/Mdl_settings.php` (8 methods)
2. Create `Version.php` from `application/modules/settings/models/Mdl_versions.php` (3 methods)
3. Complete `Setup.php` from `application/modules/setup/models/Mdl_setup.php` (12 methods, currently 0)

## Phase 3: Migrate Controllers

### Quotes Module
- Review `application/modules/quotes/controllers/Quotes.php`
- Ensure all methods in `Modules/Quotes/Http/Controllers/QuotesController.php`
- Review `application/modules/quotes/controllers/Ajax.php`
- Ensure all methods in `Modules/Quotes/Http/Controllers/AjaxController.php`

### Invoices Module
- Review `application/modules/invoices/controllers/Invoices.php`
- Review `application/modules/invoices/controllers/Ajax.php`
- Review `application/modules/invoices/controllers/Recurring.php` - MIGRATE
- Review `application/modules/invoices/controllers/Cron.php` - MIGRATE

### All Other Modules
- Systematically compare each controller file
- Ensure all actions migrated
- Verify PSR-4 naming

## Phase 4: Migrate Views

For each module:
1. List all views in `application/modules/{module}/views/`
2. Compare with `Modules/{Module}/Resources/views/`
3. Migrate missing views
4. Update view references in controllers

Example for Quotes:
```bash
# Legacy views
application/modules/quotes/views/
  - index.php
  - view.php
  - modal_create_quote.php
  - modal_copy_quote.php
  - modal_delete_quote.php
  - modal_quote_to_invoice.php
  - modal_add_quote_tax.php
  - partial_quote_table.php
  - partial_itemlist_table.php
  - partial_itemlist_table_quote_discount.php
  - partial_itemlist_responsive.php

# New views
Modules/Quotes/Resources/views/
  - (compare and ensure all are migrated)
```

## Phase 5: Assign and Migrate Unmapped Modules

### Determine Module Assignments

1. **email_templates** (2 controllers, 1 model, 4 views)
   - Suggested module: `Core` or new `Communication` module
   - Contains: Email template management

2. **upload** (1 controller, 1 model)
   - Suggested module: `Core`
   - Contains: File upload handling

3. **mailer** (1 controller, 3 views)
   - Suggested module: `Core` or `Communication`
   - Contains: Email sending functionality

4. **guest** (7 controllers!, 9 views)
   - Suggested module: New `Guest` module or `Core`
   - Contains: Public-facing invoice/quote viewing, payments
   - Controllers: Guest.php, Paypal.php, Stripe.php, Invoices.php, Payment_information.php, View.php, Quotes.php, Payments.php, Get.php

5. **reports** (1 controller, 1 model, 5 views)
   - Suggested module: New `Reports` module or `Core`
   - Contains: Report generation

6. **import** (1 controller, 1 model, 2 views)
   - Suggested module: `Core`
   - Contains: Data import functionality

7. **filter** (1 controller, 1 view)
   - Suggested module: `Core`
   - Contains: Filtering utilities

8. **welcome** (1 controller, 1 view)
   - Suggested module: `Core`
   - Contains: Welcome/landing page

### Migration Steps for Each

For each unmapped module:
1. Decide target module location
2. Create necessary directory structure
3. Migrate all models (one-to-one)
4. Migrate all controllers (one-to-one)
5. Migrate all views
6. Update routes
7. Test functionality

## Phase 6: Verification and Cleanup

### Verification Checklist

For each migrated model:
- [ ] Method count matches (legacy count = new count)
- [ ] All business logic preserved
- [ ] Calculations work correctly (test invoice/quote totals)
- [ ] PSR-4 naming compliant (no underscores)
- [ ] Follows PSR-12 standards
- [ ] Type hints added
- [ ] Documentation added
- [ ] Relationships defined
- [ ] Scopes defined where applicable

For each migrated controller:
- [ ] All actions migrated
- [ ] PSR-4 naming compliant
- [ ] View paths updated
- [ ] Model usage updated to Eloquent
- [ ] Dependency injection used

For each migrated view:
- [ ] File exists in new location
- [ ] Syntax compatible with Illuminate View
- [ ] No CodeIgniter-specific calls

### Cleanup Process

**ONLY after verification:**

1. Remove legacy model files from `application/modules/{module}/models/`
2. Remove legacy controller files from `application/modules/{module}/controllers/`
3. Remove legacy view files from `application/modules/{module}/views/`
4. Update `.gitignore` if needed
5. Document any intentionally unmigrated code

## Phase 7: Run Linters and Fix Issues

```bash
# Run all checks
composer check

# Run individual tools
composer rector    # Modernize code
composer phpcs     # Check PSR-12 compliance
composer pint      # Fix Laravel style
```

Fix all issues found by linters.

## Phase 8: Final Documentation

1. Update `MIGRATION-STATUS.md` with current state
2. Update `README.md` if needed
3. Document any deviations from one-to-one migration
4. Create list of deprecated/removed features (if any)

## Priority Order

**Critical Path (Do First):**
1. Fix PSR-4 naming violations (breaks autoloading)
2. Complete Quote model (critical for business)
3. Complete Invoice model (critical for business)
4. Complete QuoteAmount, InvoiceAmount (critical for calculations)

**High Priority:**
5. Complete all calculation models (item amounts, tax rates)
6. Complete Client, Product, Payment models (core entities)
7. Migrate unmapped modules (guest module is critical - 7 controllers!)

**Medium Priority:**
8. Complete Custom fields functionality
9. Complete Users functionality
10. Complete Core functionality

**Lower Priority:**
11. Reports, Import, Upload modules
12. View migrations
13. Documentation updates

## Success Criteria

- [ ] ZERO models in `application/modules/` with equivalents in `Modules/`
- [ ] ZERO controllers in `application/modules/` with equivalents in `Modules/`
- [ ] ZERO PSR-4 naming violations
- [ ] All linters pass
- [ ] Method counts verified for all models
- [ ] Critical calculations tested (invoice totals, quote totals, taxes)
- [ ] All modules either migrated or intentionally documented as not migrated

## Notes

- This is a **complete migration**, not a rewrite
- Every method matters - especially calculations
- PSR-4 compliance is non-negotiable
- Test calculations thoroughly - money is involved!
- Document decisions about unmapped modules
