# Refactoring Progress and Roadmap

## Executive Summary

This document tracks the progress of the massive codebase standardization effort to make the entire application look like it was written by a single person in a single day, following SOLID principles, with comprehensive tests and proper authentication.

## Completed Work

### ✅ Phase 1: Foundation and Infrastructure

1. **PSR-4 Compliance Fixed**
   - Fixed 5 namespace violations
   - Moved misplaced controller files
   - Renamed files to match class names
   - Autoload now generates 263 classes cleanly

2. **Standardization Documentation**
   - Created comprehensive STANDARDIZATION-GUIDE.md
   - Defined controller, service, FormRequest, test, and route standards
   - Documented PHPDoc requirements with @legacy-* tags
   - Created reference implementation examples

3. **Refactoring Tools**
   - Created `refactor-helper.php` script
   - Automatically analyzes controllers for standards compliance
   - Provides actionable refactoring suggestions
   - Identifies missing PHPDoc, type hints, and legacy code

4. **Reference Implementations**
   - ✅ TasksController - Perfect reference for standard controller pattern
   - ✅ UnitsController - Second complete reference implementation
   - Both include:
     - `declare(strict_types=1)`
     - Constructor property promotion with `readonly`
     - Complete PHPDoc with @legacy-* tags
     - Alphabetically sorted imports
     - Full type hints
     - Service-based architecture
     - FormRequest validation

## Remaining Work

### Scope: 48 Controllers Need Refactoring

| Module | Controllers | Status | Priority |
|--------|-------------|--------|----------|
| **Core** | 17 controllers | 0/17 complete | High |
| **Crm** | 10 controllers | 0/10 complete | High |
| **Invoices** | 5 controllers | 0/5 complete | High |
| **Products** | 4 controllers | 2/4 complete ✅ | Medium |
| **Quotes** | 2 controllers | 0/2 complete | High |
| **Projects** | 3 controllers | 1/3 complete ✅ | Medium |
| **Payments** | 3 controllers | 0/3 complete | Medium |
| **Ajax Controllers** | 7 controllers | 0/7 complete | Medium |
| **Gateway Controllers** | 2 controllers | 0/2 complete | Low |

**Total:** 3/50 controllers complete (6%)

### Critical Controllers to Refactor (Priority Order)

#### High Priority (Core CRUD Operations)
1. **QuotesController** (~323 lines)
   - Remove DB queries
   - Add comprehensive PHPDoc
   - Create QuoteRequest FormRequest
   - Move business logic to QuoteService

2. **InvoicesController** (~400+ lines)
   - Remove DB queries
   - Add comprehensive PHPDoc
   - Create InvoiceRequest FormRequest
   - Move business logic to InvoiceService

3. **ClientsController** (~300+ lines)
   - Remove legacy AdminController extends
   - Remove direct DB access
   - Add comprehensive PHPDoc
   - Already has ClientRequest ✅

4. **ProductsController** (~91 lines)
   - Remove AllowDynamicProperties
   - Remove AdminController extends
   - Add declare(strict_types=1)
   - Add PHPDoc

5. **PaymentsController**
   - Add PHPDoc
   - Create PaymentRequest
   - Standardize patterns

#### Medium Priority
6. SessionsController - Critical for auth
7. DashboardController - Main landing page
8. SettingsController - System configuration
9. UsersController - User management
10. ProjectsController - Project CRUD

#### Ajax Controllers (Special Handling Needed)
- QuotesAjaxController (~676 lines!)
- InvoicesAjaxController
- ClientsAjaxController
- ProductsAjaxController
- PaymentsAjaxController
- CoreAjaxController (Settings)
- TasksAjaxController

### FormRequest Classes Needed

**Existing (8):** ✅
- TaskRequest
- ProductRequest
- UnitRequest
- TaxRateRequest
- FamilyRequest
- ProjectRequest
- ClientRequest
- SettingsRequest

**Needed (~34 more):**
- QuoteRequest
- QuoteItemRequest
- InvoiceRequest
- InvoiceItemRequest
- PaymentRequest
- UserRequest
- EmailTemplateRequest
- CustomFieldRequest
- And ~26 more...

### Route Authentication Audit

**Status:** Not started

**Requirements:**
- Audit all ~200+ routes
- Add `['web', 'auth']` middleware to protected routes
- Document public routes (exceptions):
  - `/` - Welcome
  - `/setup` - Installation
  - `/sessions/login` - Login
  - `/sessions/passwordreset` - Password reset
  - `/guest/*` - Guest access with URL keys

**Files to Create/Update:**
- Core/Routes/web/*.php (multiple route files)
- Crm/Routes/web/*.php
- Invoices/Routes/web/*.php
- Products/Routes/web/*.php
- Quotes/Routes/web/*.php
- Projects/Routes/web/*.php (partially exists)
- Payments/Routes/web/*.php

### Test Enhancement

**Current State:**
- Tests exist but many don't use #[CoversClass()]
- Many tests only check HTTP status, not data
- No data providers for realistic test scenarios
- Missing authentication tests

**Needed:**
- Add #[CoversClass()] to ALL test files (~40 files)
- Implement data providers for edge cases
- Test actual data in responses
- Add comprehensive validation tests
- Add authentication requirement tests
- Achieve 80%+ code coverage

## Detailed Refactoring Checklist

For each controller, follow this checklist:

### Before Starting
- [ ] Run `php refactor-helper.php <controller-path>`
- [ ] Review issues identified
- [ ] Check if legacy controller exists in `application/modules/`
- [ ] Note legacy file path for @legacy-file tags

### Code Structure
- [ ] Add `declare(strict_types=1);`
- [ ] Add comprehensive class PHPDoc with @legacy-file
- [ ] Use constructor property promotion with `readonly`
- [ ] Remove `AllowDynamicProperties` attribute
- [ ] Remove `extends AdminController` or other legacy extends
- [ ] Sort use statements alphabetically
- [ ] Add imports: `Illuminate\Http\RedirectResponse`, `Illuminate\View\View`

### Method Refactoring
- [ ] Add PHPDoc to ALL methods
- [ ] Add @legacy-function and @legacy-file to each method
- [ ] Add complete type hints (parameters and returns)
- [ ] Use early returns for guard clauses
- [ ] Format code consistently (proper spacing)

### Business Logic Migration
- [ ] Move all database queries to Service layer
- [ ] Remove `$this->db->`, `DB::`, `$this->load->` calls
- [ ] Create/verify Service exists and extends BaseService
- [ ] Create FormRequest for validation rules
- [ ] Remove inline validation `$request->validate()`

### Testing
- [ ] Create/update test class with #[CoversClass()]
- [ ] Add tests for all public methods
- [ ] Use data providers for validation tests
- [ ] Test actual data, not just HTTP status
- [ ] Test authentication requirements
- [ ] Run tests: `php artisan test --filter=ControllerName`

### Routes
- [ ] Create/update route file in Module/Routes/web/
- [ ] Add authentication middleware
- [ ] Use route model binding where applicable
- [ ] Follow RESTful naming conventions
- [ ] Document any public (non-auth) routes

### Verification
- [ ] Run `composer dump-autoload`
- [ ] Run `composer check` (or individual: rector, phpcs, pint)
- [ ] Run `php refactor-helper.php <controller-path>` again
- [ ] Verify all tests pass
- [ ] Code review changes

## Time Estimates

Based on complexity analysis:

### Per Controller Type
- **Simple CRUD (e.g., Units, Families):** 1-2 hours
- **Standard Resource (e.g., Tasks, Projects):** 2-3 hours
- **Complex Resource (e.g., Quotes, Invoices):** 4-6 hours
- **Ajax Controllers:** 3-5 hours each
- **Special Controllers (Setup, Sessions):** 3-4 hours

### Total Estimates
- Controllers refactoring: **120-180 hours**
- FormRequest creation: **20-30 hours**
- Route audit and middleware: **15-20 hours**
- Test enhancement: **40-60 hours**
- Documentation updates: **10-15 hours**

**Grand Total: ~205-305 hours** (5-8 weeks full-time)

## Success Metrics

- [ ] All 50 controllers have `declare(strict_types=1)`
- [ ] All 50 controllers use property promotion with `readonly`
- [ ] All controllers have complete PHPDoc with @legacy-* tags
- [ ] Zero database queries in controllers
- [ ] All validation in FormRequest classes
- [ ] All tests have #[CoversClass()] attribute
- [ ] 80%+ code coverage
- [ ] All routes have proper authentication middleware
- [ ] Zero PSR-4 autoload warnings
- [ ] All linters pass (rector, phpcs, pint)

## Quick Start Guide

### To Refactor a Controller

1. Analyze current state:
   ```bash
   php refactor-helper.php Modules/Path/To/Controller.php
   ```

2. Review reference implementations:
   - `Modules/Projects/Controllers/TasksController.php`
   - `Modules/Products/Controllers/UnitsController.php`

3. Review standards:
   - `STANDARDIZATION-GUIDE.md`

4. Make changes following the checklist above

5. Verify:
   ```bash
   composer dump-autoload
   composer check
   php artisan test --filter=ControllerNameTest
   ```

### To Create a FormRequest

1. Use template:
   ```bash
   cp Modules/Products/Http/Requests/UnitRequest.php \
      Modules/YourModule/Http/Requests/YourEntityRequest.php
   ```

2. Update namespace and validation rules

3. Reference in controller method parameters

### To Add Authentication to Routes

1. Update route file in `Modules/{Module}/Routes/web/`

2. Wrap in middleware:
   ```php
   Route::middleware(['web', 'auth'])->group(function () {
       // protected routes
   });
   ```

3. Document any public routes (exceptions)

## Next Steps

### Immediate Priorities

1. **Week 1-2:** Refactor critical controllers (Quotes, Invoices, Clients, Products)
2. **Week 3-4:** Create missing FormRequests and enhance services
3. **Week 5-6:** Route authentication audit and implementation
4. **Week 7-8:** Test enhancement and coverage improvements

### Continuous Activities

- Run `refactor-helper.php` before and after each controller
- Keep `composer check` passing at all times
- Update this document as controllers are completed
- Document any new patterns or edge cases discovered

## Resources

- **Standards:** STANDARDIZATION-GUIDE.md
- **Helper Script:** refactor-helper.php
- **Reference Controllers:**
  - Modules/Projects/Controllers/TasksController.php
  - Modules/Products/Controllers/UnitsController.php
- **Reference Tests:**
  - Modules/Projects/Tests/Feature/TasksControllerTest.php
- **Reference FormRequests:**
  - Modules/Projects/Http/Requests/TaskRequest.php
  - Modules/Products/Http/Requests/UnitRequest.php

## Notes

This is a massive refactoring effort that will transform the entire codebase. The foundation has been laid with:
- Clear standards documented
- Reference implementations created
- Automated analysis tools built
- Comprehensive checklists defined

The work can now proceed systematically, one controller at a time, with confidence that every refactored component will be consistent with the standards.
