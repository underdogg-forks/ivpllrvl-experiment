# Refactoring Roadmap and Task List

## Overview

This document tracks the refactoring work needed to ensure code consistency across the InvoicePlane codebase.

## Approved Standards

### ✅ YES - Keep These Patterns
- **Tests**: `#[CoversClass()]` attributes on test classes
- **Tests**: Data providers for realistic test scenarios
- **Tests**: Test actual data, not just HTTP status codes
- **PHPDoc**: Complete documentation with `@legacy-function` and `@legacy-file` tags
- **Services**: Move database queries to services
- **FormRequests**: Use FormRequest classes for validation (when appropriate)

### ❌ NO - Do NOT Use These Patterns
- **NO** `declare(strict_types=1);` - Do not add this
- **NO** Property promotion with `readonly` - Use traditional constructors
- **NO** RESTful route patterns - Keep existing routing patterns
- **NO** Splitting `form()` methods - Keep combined form() method for create/edit

## Current State

### Controllers Modified (Now Reverted)
- ✅ TasksController - Reverted to original
- ✅ UnitsController - Reverted to original
- ✅ FamiliesController - Reverted to original

### Documentation Updated
- ✅ STANDARDIZATION-GUIDE.md - Updated with correct patterns
- ✅ refactor-helper.php - Updated to check for correct patterns only

## Task List

### Phase 1: Test Infrastructure ✅
- [x] Create test base classes
- [x] Establish test patterns with `#[CoversClass()]`
- [x] Document test standards in guide

### Phase 2: Documentation
- [x] Update STANDARDIZATION-GUIDE.md with correct patterns
- [x] Remove incorrect patterns from helper script
- [x] Create task list (this document)

### Phase 3: Controller Refactoring (50 Controllers)

Priority should be on:
1. Adding comprehensive PHPDoc blocks with `@legacy-*` tags
2. Moving database queries to services
3. Adding FormRequest validation (where appropriate)
4. Creating comprehensive tests with `#[CoversClass()]`

#### Core Module (17 controllers)
- [ ] UsersAjaxController
- [ ] LayoutController
- [ ] ImportController
- [ ] SetupController
- [ ] UserClientsController
- [ ] CustomValuesController
- [ ] UploadController
- [ ] UsersController
- [ ] DashboardController
- [ ] GuestController (PSR-4 fixed)
- [ ] MailerController (PSR-4 fixed)
- [ ] SettingsController
- [ ] TaxRatesController
- [ ] EmailTemplatesController
- [ ] SessionsController
- [ ] WelcomeController
- [ ] CustomFieldsController
- [ ] ReportsController (PSR-4 fixed)
- [ ] SettingsAjaxController
- [ ] VersionsController
- [ ] GetController

#### CRM Module (10 controllers)
- [ ] UserClientsController
- [ ] ClientsController
- [ ] PaymentsController
- [ ] ClientsAjaxController (PSR-4 fixed)
- [ ] ViewController
- [ ] InvoicesController
- [ ] PaymentInformationController
- [ ] GuestController
- [ ] Gateways/StripeController
- [ ] Gateways/PaypalController
- [ ] QuotesController
- [ ] GetController

#### Invoices Module (5 controllers)
- [ ] InvoicesAjaxController
- [ ] CronController
- [ ] InvoiceGroupsController
- [ ] InvoicesController
- [ ] RecurringController

#### Products Module (4 controllers)
- [ ] UnitsController
- [ ] ProductsAjaxController
- [ ] ProductsController
- [ ] FamiliesController
- [ ] TaxRatesController (in Core?)

#### Quotes Module (2 controllers)
- [ ] QuotesAjaxController
- [ ] QuotesController

#### Projects Module (3 controllers)
- [ ] TasksAjaxController (PSR-4 fixed)
- [ ] TasksController
- [ ] ProjectsController

#### Payments Module (3 controllers)
- [ ] PaymentsAjaxController
- [ ] PaymentsController
- [ ] PaymentMethodsController

### Phase 4: Test Coverage

For each controller, ensure:
- [ ] Test class exists with `#[CoversClass()]` attribute
- [ ] Data providers used for validation scenarios
- [ ] Tests verify actual data, not just HTTP status
- [ ] Edge cases covered
- [ ] Authentication requirements tested

### Phase 5: Service Layer Enhancement

Ensure all controllers:
- [ ] Have no direct database queries (all in services)
- [ ] Use services for business logic
- [ ] Keep controllers thin (HTTP handling only)

## Success Metrics

- [ ] All controllers have comprehensive PHPDoc with `@legacy-*` tags
- [ ] All controllers delegate database queries to services
- [ ] All test classes use `#[CoversClass()]` attribute
- [ ] Tests use data providers for validation scenarios
- [ ] 80%+ code coverage achieved
- [ ] Zero inline validation (use FormRequests where appropriate)
- [ ] All use statements alphabetically sorted

## Time Estimates

- **PHPDoc addition**: ~30 minutes per controller
- **Service extraction**: ~1-2 hours per controller
- **Test creation**: ~2-3 hours per controller
- **FormRequest creation**: ~30 minutes each

**Total estimated**: 150-250 hours for complete refactoring

## Reference Implementations

Check these files for correct patterns:
- `Modules/Projects/Controllers/TasksController.php` - Controller example
- `Modules/Products/Controllers/FamiliesController.php` - form() method pattern
- `Modules/Projects/Tests/Feature/TasksControllerTest.php` - Test example with `#[CoversClass()]`
- `STANDARDIZATION-GUIDE.md` - Complete standards reference

## Notes

- **NO** strict types declarations
- **NO** property promotion with readonly
- **NO** RESTful route splitting
- **YES** to comprehensive tests with data providers
- **YES** to PHPDoc with legacy references
- **YES** to service layer architecture
