# Migration TODO List - Remaining Work

**Last Updated:** 2025-10-29
**Current Status:** Phase 2 COMPLETE (95%), Phase 3 Infrastructure Ready

## ✅ Phase 2 Model Migration - COMPLETE

**Completed:** 38+ models migrated, 200+ methods, 8/8 modules (95%)

All core business models have been successfully migrated:
- ✅ Quotes Module (5 models)
- ✅ Invoices Module (9 models)
- ✅ Products Module (4 models)
- ✅ Payments Module (3 models)
- ✅ CRM Module (5 models)
- ✅ Users Module (2 models)
- ✅ Custom/Core Module (10+ models)

See **PHASE-2-COMPLETION-REPORT.md** for full details.

---

## 🔄 Phase 3: Controller Migration - IN PROGRESS

**Status:** Infrastructure setup complete, controllers pending migration
**Total Controllers:** 44
**Completed:** 0/44
**Estimated Time:** 40-60 hours

### Testing Infrastructure ✅ COMPLETE

- [x] PHPUnit 11.x installed and configured
- [x] Test bootstrap file created
- [x] Test directory structure created
- [x] Test standards documented
- [x] Example test patterns provided
- [x] PHASE-3-IMPLEMENTATION-PLAN.md created

### Controller Migration Checklist

For each controller:
- [ ] Migrate all methods from legacy controller
- [ ] Add PHPDoc with `@legacy-function`, `@legacy-file`, `@legacy-line`
- [ ] Create feature test class with `#[CoversClass]`
- [ ] Write test for each method with `#[Test]` attribute
- [ ] Use `it_` prefix for test method names
- [ ] Follow Arrange-Act-Assert pattern
- [ ] Test data integrity, not just HTTP status
- [ ] Verify all tests pass

---

## Priority 1: Core Business Controllers (15 controllers)

### Quotes Module (2 controllers)

#### [ ] QuotesController - 8 methods
**Source:** `application/modules/quotes/controllers/Quotes.php`
**Time Estimate:** 2-3 hours

Methods to migrate:
- [ ] `index()` - Redirect to status view
- [ ] `status(string $status, int $page)` - Filter by status
- [ ] `view(int $quote_id)` - Display quote details
- [ ] `delete(int $quote_id)` - Delete quote
- [ ] `generate_pdf(int $quote_id, bool $stream, ?string $template)` - PDF generation
- [ ] `delete_quote_tax(int $quote_id, int $tax_id)` - Remove tax
- [ ] `recalculate_all_quotes()` - Batch recalculation

Tests to create:
- [ ] it_redirects_to_all_status_view_from_index
- [ ] it_displays_only_draft_quotes_when_draft_status_selected
- [ ] it_displays_quote_details_with_items_and_amounts
- [ ] it_returns_404_when_viewing_non_existent_quote
- [ ] it_deletes_quote_and_all_related_records
- [ ] it_generates_pdf_with_correct_quote_data
- [ ] it_removes_tax_rate_and_recalculates_quote
- [ ] it_recalculates_all_quotes_successfully

#### [ ] QuotesAjaxController - Ajax operations
**Source:** `application/modules/quotes/controllers/Ajax.php`
**Time Estimate:** 2-3 hours

### Invoices Module (5 controllers)

#### [ ] InvoicesController - 15+ methods
**Source:** `application/modules/invoices/controllers/Invoices.php`
**Time Estimate:** 4-5 hours

Methods include: index, status, view, create, edit, delete, generate_pdf, archive, download, etc.

#### [ ] InvoicesAjaxController
**Source:** `application/modules/invoices/controllers/Ajax.php`
**Time Estimate:** 3-4 hours

#### [ ] InvoicesCronController  
**Source:** `application/modules/invoices/controllers/Cron.php`
**Time Estimate:** 1-2 hours

#### [ ] RecurringController
**Source:** `application/modules/invoices/controllers/Recurring.php`
**Time Estimate:** 1-2 hours

#### [ ] InvoiceGroupsController
**Source:** `application/modules/invoice_groups/controllers/Invoice_groups.php`
**Time Estimate:** 1-2 hours

### CRM Module (8 controllers)

#### [ ] ClientsController
**Source:** `application/modules/clients/controllers/Clients.php`
**Time Estimate:** 3-4 hours

#### [ ] ClientNotesController
**Source:** `application/modules/clients/controllers/` (check for notes controller)
**Time Estimate:** 1 hour

#### [ ] ProjectsController
**Source:** `application/modules/projects/controllers/Projects.php`
**Time Estimate:** 2-3 hours

#### [ ] TasksController
**Source:** `application/modules/tasks/controllers/Tasks.php`
**Time Estimate:** 2-3 hours

#### [ ] UserClientsController
**Source:** `application/modules/user_clients/controllers/User_clients.php`
**Time Estimate:** 1-2 hours

#### [ ] GuestController
**Source:** `application/modules/guest/controllers/Guest.php`
**Time Estimate:** 2-3 hours

#### [ ] GuestPaymentsController
**Source:** `application/modules/guest/controllers/Payments.php`
**Time Estimate:** 2 hours

#### [ ] GuestInvoicesController
**Source:** `application/modules/guest/controllers/Invoices.php`
**Time Estimate:** 2 hours

---

## Priority 2: System Management Controllers (13 controllers)

### Core Module

- [ ] SettingsController - Application settings
- [ ] DashboardController - Dashboard display
- [ ] LayoutController - Layout management  
- [ ] SetupController - Installation wizard
- [ ] EmailTemplatesController - Email templates
- [ ] CustomFieldsController - Custom field management
- [ ] CustomValuesController - Custom field values
- [ ] UploadController - File upload handling
- [ ] MailerController - Email sending
- [ ] ImportController - Data import
- [ ] ReportsController - Report generation
- [ ] FilterController - Filtering utilities
- [ ] WelcomeController - Welcome/landing page

**Time Estimate:** 10-15 hours total

---

## Priority 3: Supporting Features (16 controllers)

### Payments Module (3 controllers)
- [ ] PaymentsController
- [ ] PaymentMethodsController  
- [ ] MerchantController (PayPal/Stripe integration)

### Products Module (5 controllers)
- [ ] ProductsController
- [ ] FamiliesController
- [ ] UnitsController
- [ ] TaxRatesController
- [ ] ProductsAjaxController

### Users Module (3 controllers)
- [ ] UsersController
- [ ] SessionsController
- [ ] UserClientsController (if separate from CRM)

### Guest Module (5 controllers)
- [ ] GuestQuotesController
- [ ] GuestPaypalController
- [ ] GuestStripeController
- [ ] GuestPaymentInformationController
- [ ] GuestViewController

**Time Estimate:** 15-20 hours total

---

## Phase 3 Testing Requirements

For EACH controller method, create tests that verify:

### Required Test Coverage:
- [ ] **Happy Path** - Valid input produces expected output
- [ ] **Authentication** - Unauthenticated users are redirected
- [ ] **Authorization** - Unauthorized users receive 403
- [ ] **Validation** - Required fields validated, data types checked
- [ ] **Edge Cases** - Empty results, non-existent resources (404)
- [ ] **Data Integrity** - Related records handled, calculations accurate

### Test Naming Convention:
```php
public function it_<action>_<expected_result>_when_<condition>(): void
```

Examples:
- `it_displays_list_of_quotes_when_user_is_authenticated()`
- `it_returns_404_when_quote_not_found()`
- `it_creates_invoice_with_correct_total_amount()`

---

## Documentation Updates Required

- [ ] Update .github/copilot-instructions.md with Phase 3 progress
- [ ] Update MIGRATION-STATUS.md with controller migration status
- [ ] Create controller-specific migration notes as needed
- [ ] Update README with testing instructions

---

## Success Criteria for Phase 3

- [ ] All 44 controllers migrated to PSR-4
- [ ] Every method has PHPDoc with legacy references
- [ ] Comprehensive test suite with 80%+ coverage
- [ ] All tests passing
- [ ] No syntax errors
- [ ] PSR-12 compliant
- [ ] Routes updated to new controllers
- [ ] Documentation complete

---

## Next Immediate Steps

1. **Start with QuotesController** - Simplest, good example
2. **Create complete test suite** for QuotesController
3. **Verify pattern works** before continuing
4. **Move to InvoicesController** - Most critical
5. **Continue systematically** through Priority 1, 2, 3

**Estimated Completion Time:** 40-60 hours of focused development

See **PHASE-3-IMPLEMENTATION-PLAN.md** for detailed guidance, patterns, and examples.
- [ ] `getArchives($invoice_number)` - Archive retrieval
- [ ] `delete($invoice_id)` - Delete with orphan cleanup
- [ ] `markViewed($invoice_id)` - Mark as viewed
- [ ] `markSent($invoice_id)` - Mark as sent
- [ ] `generateInvoiceNumberIfApplicable($invoice_id)` - Number generation
- [ ] `markRecurringCreated($invoice_id)` - Recurring flag
- [ ] Additional scopes (overdue, sumex, guest_visible, etc.)
- Source: `application/modules/invoices/models/Mdl_invoices.php`

#### InvoiceAmount.php (9 methods) - CRITICAL FOR CALCULATIONS
- [ ] `calculate($invoice_id)` - Master calculation
- [ ] `calculateDiscount($invoice_id, $invoice_total)` - Discount calc
- [ ] `getGlobalDiscount($invoice_id)` - Global discount
- [ ] `calculateInvoiceTaxes($invoice_id)` - Tax calculations
- [ ] `getTotalInvoiced($period)` - Total for period
- [ ] `getTotalPaid($period)` - Total paid for period
- [ ] `getTotalBalance($period)` - Balance for period
- [ ] `getStatusTotals($period)` - Totals by status
- [ ] `getAmountBreadcrumb($invoice_id)` - Amount history
- Source: `application/modules/invoices/models/Mdl_invoice_amounts.php`

#### Item.php (7 methods)
- [ ] `defaultSelect()` - Select with joins
- [ ] `defaultOrderBy()` - Sort by order
- [ ] `defaultJoin()` - Join with amounts, products, tax rates
- [ ] `validationRules()` - Validation rules
- [ ] `save($id, $db_array, &$global_discount)` - Save with calculations
- [ ] `delete($item_id)` - Delete with recalculation
- [ ] `getItemsSubtotal($invoice_id)` - Subtotal calculation
- Source: `application/modules/invoices/models/Mdl_items.php`

#### InvoiceTaxRate.php (4 methods)
- [ ] `defaultSelect()` - Select with tax rate details
- [ ] `defaultJoin()` - Join with tax rates
- [ ] `save($id, $db_array)` - Save with calculations
- [ ] `validationRules()` - Validation rules
- Source: `application/modules/invoices/models/Mdl_invoice_tax_rates.php`

#### ItemAmount.php (1 method)
- [ ] `calculate($item_id, &$global_discount)` - Calculate item amounts
- Source: `application/modules/invoices/models/Mdl_item_amounts.php`

#### InvoiceSumex.php (2 methods remaining)
- [ ] `validationRules()` - Validation for SUMEX data
- [ ] Additional SUMEX-specific methods
- Source: `application/modules/invoices/models/Mdl_invoice_sumex.php`

#### InvoicesRecurring.php (7 methods remaining)
- [ ] `defaultSelect()` - Select with frequency details
- [ ] `defaultJoin()` - Join with invoices
- [ ] `validationRules()` - Validation rules
- [ ] `create($invoice_id, $db_array)` - Create recurring config
- [ ] `getNextRecurDate($recurring_id)` - Calculate next occurrence
- [ ] `getRecurringInvoices()` - Get all recurring invoices
- [ ] `generateRecurringInvoices($recurring_id)` - Generate instances
- Source: `application/modules/invoices/models/Mdl_invoices_recurring.php`

#### Template.php (3 methods)
- [ ] `defaultSelect()` - Select templates
- [ ] `validationRules()` - Validation rules
- [ ] `getTemplateList()` - Get available templates
- Source: `application/modules/invoices/models/Mdl_templates.php`

#### InvoiceGroup.php (6 methods) - HIGH PRIORITY (Number Generation)
- [ ] `defaultSelect()` - Select with prefix/suffix
- [ ] `validationRules()` - Validation rules
- [ ] `generateInvoiceNumber($invoice_group_id)` - Generate next number
- [ ] `getNextInvoiceNumber($invoice_group_id)` - Get next number
- [ ] `incrementNextInvoiceNumber($invoice_group_id)` - Increment counter
- [ ] `getDefaultGroup()` - Get default group
- Source: `application/modules/invoice_groups/models/Mdl_invoice_groups.php`

### [ ] Client Model (Priority 3) - CRITICAL BUSINESS ENTITY
**Time Estimate:** 3-4 hours

- [ ] **Client.php** - 15 methods
  - [ ] `defaultSelect()` - Select with address, custom fields
  - [ ] `defaultJoin()` - Join with users, custom fields
  - [ ] `validationRules()` - Validation rules
  - [ ] `create($db_array)` - Create with custom fields
  - [ ] `save($id, $db_array)` - Save with custom fields
  - [ ] `delete($client_id)` - Delete with cleanup
  - [ ] `getClientsDropdown()` - Get list for dropdowns
  - [ ] `getActiveClientsDropdown()` - Get active clients
  - [ ] `getCustomFieldValues($client_id)` - Get custom field values
  - [ ] `isActive()` - Scope for active clients
  - [ ] `isInactive()` - Scope for inactive clients
  - [ ] `search($term)` - Search clients
  - [ ] `getBalanceTotal($client_id)` - Get total balance
  - [ ] `getInvoiceTotal($client_id)` - Get invoice total
  - [ ] `getPaidTotal($client_id)` - Get paid total
  - Source: `application/modules/clients/models/Mdl_clients.php`

### [ ] Payment Model (Priority 3) - CRITICAL BUSINESS ENTITY
**Time Estimate:** 2-3 hours

- [ ] **Payment.php** - 10 methods
  - [ ] `defaultSelect()` - Select with method, invoice
  - [ ] `defaultJoin()` - Join with methods, invoices
  - [ ] `validationRules()` - Validation rules
  - [ ] `create($db_array)` - Create with logging
  - [ ] `save($id, $db_array)` - Save with recalculation
  - [ ] `delete($payment_id)` - Delete with recalculation
  - [ ] `getPaymentMethods()` - Get available methods
  - [ ] `getInvoicePayments($invoice_id)` - Get payments for invoice
  - [ ] `getTotalPaidForInvoice($invoice_id)` - Get total paid
  - [ ] `byInvoice($invoice_id)` - Scope for invoice payments
  - Source: `application/modules/payments/models/Mdl_payments.php`

## Medium Priority Items

### [ ] Products Module
**Time Estimate:** 4-5 hours

- [ ] **Product.php** - 7 methods
- [ ] **TaxRate.php** - 3 methods (IMPORTANT - used in calculations)
- [ ] **Family.php** - 3 methods
- [ ] **Unit.php** - 4 methods

### [ ] Users Module
**Time Estimate:** 3-4 hours

- [ ] **User.php** - 11 methods
- [ ] **UserClient.php** - 7 methods
- [ ] **Session.php** - 1 method

### [ ] Custom Fields Module
**Time Estimate:** 6-8 hours

- [ ] **CustomField.php** - 17 methods
- [ ] **CustomValue.php** - 16 methods
- [ ] **ClientCustom.php** - 9 methods
- [ ] **InvoiceCustom.php** - 5 methods
- [ ] **QuoteCustom.php** - 5 methods
- [ ] **PaymentCustom.php** - 6 methods
- [ ] **UserCustom.php** - 6 methods

### [ ] CRM Module (Supporting)
**Time Estimate:** 4-5 hours

- [ ] **Project.php** - 6 methods
- [ ] **Task.php** - 14 methods
- [ ] **ClientNote.php** - 4 methods

### [ ] Payment Supporting
**Time Estimate:** 1-2 hours

- [ ] **PaymentMethod.php** - 3 methods
- [ ] **PaymentLog.php** - 3 methods

## Lower Priority Items

### [ ] Core/System Module
**Time Estimate:** 3-4 hours

- [ ] **Setting.php** - 8 methods
- [ ] **Setup.php** - 12 methods
- [ ] **Version.php** - 3 methods

## Phase 3: Controller Migration
**Time Estimate:** 20-30 hours

### Controllers to Migrate (44 total)

**Core Module (13 controllers):**
- [ ] AjaxController
- [ ] CustomFieldsController
- [ ] CustomValuesController
- [ ] DashboardController
- [ ] EmailTemplatesController
- [ ] ImportController
- [ ] LayoutController
- [ ] MailerController
- [ ] ReportsController
- [ ] SettingsController
- [ ] SetupController
- [ ] UploadController
- [ ] VersionsController

**CRM Module (11 controllers):**
- [ ] AjaxController
- [ ] ClientsController
- [ ] GetController
- [ ] GuestController
- [ ] InvoicesController (guest)
- [ ] PaymentInformationController
- [ ] PaymentsController (guest)
- [ ] ProjectsController
- [ ] QuotesController (guest)
- [ ] TasksController
- [ ] UserClientsController
- [ ] ViewController

**Invoices Module (5 controllers):**
- [ ] AjaxController
- [ ] CronController
- [ ] InvoiceGroupsController
- [ ] InvoicesController
- [ ] RecurringController

**Payments Module (3 controllers):**
- [ ] AjaxController
- [ ] PaymentMethodsController
- [ ] PaymentsController

**Products Module (5 controllers):**
- [ ] AjaxController
- [ ] FamiliesController
- [ ] ProductsController
- [ ] TaxRatesController
- [ ] UnitsController

**Quotes Module (2 controllers):**
- [ ] AjaxController
- [ ] QuotesController

**Users Module (3 controllers):**
- [ ] AjaxController
- [ ] SessionsController
- [ ] UsersController

## Phase 4: Views Verification
**Status:** Views already migrated
**Action Required:** Verify view paths in migrated controllers

- [ ] Verify all view paths use module::view syntax
- [ ] Check for any missing views
- [ ] Update view calls in controllers

## Phase 5: Unmapped Modules Assignment

Need to assign these legacy modules to new modules:

- [ ] **email_templates** → Core or Communication module
- [ ] **upload** → Core module
- [ ] **mailer** → Core or Communication module
- [ ] **guest** → New Guest module or CRM module (7 controllers!)
- [ ] **reports** → New Reports module or Core module
- [ ] **import** → Core module
- [ ] **filter** → Core module
- [ ] **welcome** → Core module

## Phase 6: Verification and Cleanup

- [ ] Run PHP linter on all migrated files
- [ ] Verify method counts match for all models
- [ ] Test critical calculations:
  - [ ] Quote totals
  - [ ] Invoice totals
  - [ ] Tax calculations
  - [ ] Discount calculations
  - [ ] Payment allocations
- [ ] Check all relationships are defined
- [ ] Verify all scopes work correctly
- [ ] Remove legacy files (ONLY after verification)

## Phase 7: Run Linters and Fix Issues

```bash
# Run all checks
composer check

# Individual tools
composer rector    # Modernize code
composer phpcs     # Check PSR-12 compliance  
composer pint      # Fix Laravel style
```

- [ ] Fix all rector issues
- [ ] Fix all phpcs issues
- [ ] Fix all pint issues
- [ ] Ensure no PSR-4 naming violations
- [ ] Ensure proper type hints everywhere

## Phase 8: Final Documentation

- [ ] Update MIGRATION-STATUS.md
- [ ] Update README.md
- [ ] Update .github/copilot-instructions.md
- [ ] Document any deviations from one-to-one migration
- [ ] Create list of deprecated/removed features
- [ ] Update API documentation if exists

## Quick Reference: File Locations

**Legacy Models:**
- Quotes: `application/modules/quotes/models/`
- Invoices: `application/modules/invoices/models/`
- Products: `application/modules/products/models/`, `application/modules/families/models/`, etc.
- Clients: `application/modules/clients/models/`
- Payments: `application/modules/payments/models/`
- Users: `application/modules/users/models/`
- Custom Fields: `application/modules/custom_fields/models/`

**New Models:**
- Quotes: `Modules/Quotes/Entities/`
- Invoices: `Modules/Invoices/Entities/`
- Products: `Modules/Products/Entities/`
- CRM: `Modules/Crm/Entities/`
- Payments: `Modules/Payments/Entities/`
- Users: `Modules/Users/Entities/`
- Custom: `Modules/Custom/Entities/`
- Core: `Modules/Core/Entities/`

## Migration Commands

```bash
# Check syntax
php -l Modules/Module/Entities/Model.php

# Run autoload dump
composer dump-autoload

# Format code
composer pint

# Check code quality
composer check
```

## Notes

- Always preserve business logic EXACTLY - this is critical for calculations
- Test calculations thoroughly - money is involved!
- Use static methods for utility functions
- Add type hints to all parameters and return values
- Follow PSR-12 coding standards
- Document complex business logic with comments

## Progress Tracking

**Models:** 2/40+ complete (5%)
**Controllers:** 0/44 complete (0%)
**Views:** 393/393 migrated (100%)
**Overall Phase 2:** ~5% complete

**Estimated Remaining Time:** 60-80 hours of focused development work
