# Route Coverage Analysis

**Last Updated:** 2025-11-02
**Total Routes:** 203 routes across all modules
**Purpose:** Track test coverage for all application routes

## Summary Statistics

| Module | Route Files | Total Routes | Estimated Coverage | Status |
|--------|-------------|--------------|-------------------|---------|
| Core | 15 files | 108 routes | ~50% | ⚠️ Partial |
| Invoices | 2 files | 36 routes | ~70% | ✅ Good |
| Quotes | 1 file | 28 routes | ~80% | ✅ Good |
| CRM | 1 file | 11 routes | ~60% | ⚠️ Partial |
| Products | 3 files | 11 routes | ~60% | ⚠️ Partial |
| Payments | 2 files | 9 routes | ~40% | ⚠️ Needs Work |
| Projects | 2 files | 9 routes | ~20% | ❌ Poor |
| **Total** | **26 files** | **212 routes** | **~55%** | **⚠️** |

## Module Breakdown

### Core Module (108 routes)

#### custom-fields.php (4 routes)
- `GET /custom-fields` - List custom fields
- `GET /custom-fields/form/{id?}` - Custom field form
- `POST /custom-fields/save` - Save custom field
- `POST /custom-fields/delete/{id}` - Delete custom field

**Test Coverage:** ✅ Good - CustomFieldsControllerTest.php exists

#### custom-values.php (5 routes)
- `GET /custom-values` - List custom values
- `GET /custom-values/form/{id?}` - Custom value form
- `POST /custom-values/save` - Save custom value
- `POST /custom-values/delete/{id}` - Delete custom value
- `GET /custom-values/by-field/{field_id}` - Get values by field

**Test Coverage:** ✅ Good - CustomValuesControllerTest.php exists

#### dashboard.php (1 route)
- `GET /dashboard` - Main dashboard

**Test Coverage:** ✅ Good - DashboardControllerTest.php exists

#### email-templates.php (4 routes)
- `GET /email-templates` - List email templates
- `GET /email-templates/form/{id}` - Edit email template
- `POST /email-templates/save/{id}` - Save email template
- `GET /email-templates/preview/{id}` - Preview email template

**Test Coverage:** ✅ Good - EmailTemplatesControllerTest.php exists

#### filter.php (15 routes)
- Various AJAX filter endpoints for:
  - Clients, Invoices, Quotes, Products
  - Payments, Users, Custom fields
  - Invoice groups, Tax rates, etc.

**Test Coverage:** ⚠️ Partial - Some coverage in CoreAjaxControllerTest.php

#### guest.php (29 routes)
- Public-facing routes (no auth required):
  - `GET /guest/view/invoice/{invoice_url_key}`
  - `GET /guest/view/quote/{quote_url_key}`
  - `GET /guest/payment/invoice/{invoice_url_key}`
  - Various payment gateway callbacks
  - PDF generation endpoints
  - Payment confirmation pages

**Test Coverage:** ✅ Good - GuestControllerTest.php exists

#### import.php (3 routes)
- `GET /import` - Import page
- `POST /import/process` - Process import
- `POST /import/validate` - Validate import

**Test Coverage:** ⚠️ Minimal - ImportControllerTest.php has basic coverage

#### mailer.php (4 routes)
- `POST /mailer/test` - Test email configuration
- `POST /mailer/invoice/{id}` - Email invoice
- `POST /mailer/quote/{id}` - Email quote
- `POST /mailer/payment-receipt/{id}` - Email payment receipt

**Test Coverage:** ✅ Good - MailerControllerTest.php exists

#### sessions.php (5 routes)
- `GET /sessions/login` - Login page
- `POST /sessions/do_login` - Process login
- `GET /sessions/logout` - Logout
- `GET /sessions/passwordreset` - Password reset page
- `POST /sessions/passwordreset` - Process password reset

**Test Coverage:** ⚠️ Not found - Needs SessionsControllerTest.php

#### settings.php (4 routes)
- `GET /settings` - Settings page
- `GET /settings/{tab}` - Settings tab
- `POST /settings/save` - Save settings
- `POST /settings/ajax/load` - AJAX load settings

**Test Coverage:** ✅ Good - SettingsControllerTest.php exists

#### setup.php (9 routes)
- `GET /setup` - Setup wizard
- `POST /setup/database` - Database setup
- `POST /setup/tables` - Create tables
- `POST /setup/account` - Create admin account
- `POST /setup/finish` - Complete setup
- Various setup validation endpoints

**Test Coverage:** ⚠️ Minimal - SetupControllerTest.php has basic coverage

#### tax-rates.php (4 routes)
- `GET /tax-rates` - List tax rates
- `GET /tax-rates/form/{id?}` - Tax rate form
- `POST /tax-rates/save` - Save tax rate
- `POST /tax-rates/delete/{id}` - Delete tax rate

**Test Coverage:** ⚠️ Not found - Integrated in Products module

#### upload.php (5 routes)
- `POST /upload/logo` - Upload logo
- `POST /upload/invoice-logo` - Upload invoice logo
- `POST /upload/attachment` - Upload attachment
- `POST /upload/delete` - Delete upload
- `GET /uploads/{file}` - Serve upload

**Test Coverage:** ⚠️ Minimal - UploadControllerTest.php has basic coverage

#### user-clients.php (4 routes)
- `GET /user-clients` - List user's clients
- `POST /user-clients/assign/{user_id}/{client_id}` - Assign client
- `POST /user-clients/unassign/{user_id}/{client_id}` - Unassign client
- `GET /user-clients/by-user/{user_id}` - Get user's clients

**Test Coverage:** ✅ Good - UserClientsControllerTest.php exists

#### users.php (11 routes)
- `GET /users` - List users
- `GET /users/form/{id?}` - User form
- `POST /users/save` - Save user
- `POST /users/delete/{id}` - Delete user
- `GET /users/view/{id}` - View user
- `POST /users/change-password/{id}` - Change password
- Various user management endpoints

**Test Coverage:** ✅ Good - UsersControllerTest.php exists

#### welcome.php (1 route)
- `GET /` - Welcome/home page

**Test Coverage:** ✅ Good - WelcomeControllerTest.php exists

### Invoices Module (36 routes)

#### invoice-groups.php (3 routes)
- `GET /invoice-groups` - List invoice groups
- `GET /invoice-groups/form/{id?}` - Invoice group form
- `POST /invoice-groups/save` - Save invoice group

**Test Coverage:** ✅ Good - InvoiceGroupsControllerTest.php exists

#### invoices.php (33 routes)
- `GET /invoices` - List invoices
- `GET /invoices/index` - Index redirect
- `GET /invoices/status/{status}` - Filter by status
- `GET /invoices/view/{id}` - View invoice
- `GET /invoices/create` - Create invoice form
- `POST /invoices/save` - Save invoice
- `POST /invoices/delete/{id}` - Delete invoice
- `POST /invoices/delete-tax/{invoice_id}/{tax_id}` - Delete tax
- `GET /invoices/generate-pdf/{id}` - Generate PDF
- AJAX endpoints for:
  - Create, Copy, Save
  - Change user, Change client
  - Delete items, Save items
  - Add tax rates
  - Recurring invoices
  - Email operations
- Modal endpoints

**Test Coverage:** ✅ Excellent - InvoicesControllerTest.php & InvoicesAjaxControllerTest.php exist

### Quotes Module (28 routes)

#### quotes.php (28 routes)
- `GET /quotes` - List quotes
- `GET /quotes/index` - Index redirect
- `GET /quotes/status/{status}` - Filter by status (6 statuses)
- `GET /quotes/view/{id}` - View quote
- `POST /quotes/delete/{id}` - Delete quote
- `POST /quotes/delete-tax/{quote_id}/{tax_id}` - Delete tax
- `POST /quotes/recalculate-all` - Recalculate all quotes
- AJAX endpoints for:
  - Save, Create, Copy
  - Change user, Change client
  - Delete items, Get items
  - Save tax rates
  - Quote to invoice conversion
- Modal endpoints (copy, create, change user/client)
- `GET /quotes/generate-pdf/{id}` - Generate PDF

**Test Coverage:** ✅ Excellent - QuotesControllerTest.php & QuotesAjaxControllerTest.php exist

### CRM Module (11 routes)

#### clients.php (11 routes)
- `GET /clients` - List clients
- `GET /clients/status/{status}` - Filter by status
- `GET /clients/view/{id}` - View client
- `GET /clients/form/{id?}` - Client form
- `POST /clients/save` - Save client
- `POST /clients/delete/{id}` - Delete client
- AJAX endpoints for:
  - Create, Update
  - Get client data
  - Modal forms
- Payment information routes

**Test Coverage:** ✅ Good - ClientsControllerTest.php exists, plus specialized tests

### Products Module (11 routes)

#### families.php (3 routes)
- `GET /families` - List product families
- `GET /families/form/{id?}` - Family form
- `POST /families/save` - Save family

**Test Coverage:** ✅ Good - FamiliesControllerTest.php exists

#### products.php (5 routes)
- `GET /products` - List products
- `GET /products/form/{id?}` - Product form
- `POST /products/save` - Save product
- `POST /products/delete/{id}` - Delete product
- AJAX lookup endpoint

**Test Coverage:** ✅ Good - ProductsControllerTest.php exists

#### units.php (3 routes)
- `GET /units` - List units
- `GET /units/form/{id?}` - Unit form
- `POST /units/save` - Save unit

**Test Coverage:** ✅ Good - UnitsControllerTest.php exists

### Payments Module (9 routes)

#### payment-methods.php (3 routes)
- `GET /payment-methods` - List payment methods
- `GET /payment-methods/form/{id?}` - Payment method form
- `POST /payment-methods/save` - Save payment method

**Test Coverage:** ⚠️ Partial - PaymentMethodsControllerTest.php exists

#### payments.php (6 routes)
- `GET /payments` - List payments
- `GET /payments/form/{invoice_id?}` - Payment form
- `POST /payments/save` - Save payment
- `POST /payments/delete/{id}` - Delete payment
- AJAX create/update endpoints

**Test Coverage:** ⚠️ Partial - PaymentsControllerTest.php exists but limited

### Projects Module (9 routes)

#### projects.php (4 routes)
- `GET /projects` - List projects
- `GET /projects/view/{id}` - View project
- `GET /projects/form/{id?}` - Project form
- `POST /projects/save` - Save project

**Test Coverage:** ❌ Minimal - ProjectsControllerTest.php has very limited coverage

#### tasks.php (5 routes)
- `GET /tasks` - List tasks
- `GET /tasks/by-project/{project_id}` - Tasks by project
- `GET /tasks/form/{id?}` - Task form
- `POST /tasks/save` - Save task
- `POST /tasks/delete/{id}` - Delete task

**Test Coverage:** ❌ Minimal - TasksControllerTest.php has very limited coverage

## Coverage Gaps by Priority

### Priority 1: Critical Routes Without Tests (High Risk)

1. **Sessions/Authentication (5 routes) - CRITICAL**
   - Login, logout, password reset
   - No test coverage found
   - **Recommendation:** Create SessionsControllerTest.php immediately

2. **Payment Gateway Callbacks (Guest routes)**
   - PayPal, Stripe callbacks
   - Partial coverage in GuestControllerTest.php
   - **Recommendation:** Add integration tests for each gateway

### Priority 2: Core Features Needing Better Coverage

1. **Import System (3 routes)**
   - Basic test exists but incomplete
   - **Recommendation:** Add tests for CSV parsing, validation errors

2. **Setup Wizard (9 routes)**
   - Minimal coverage
   - **Recommendation:** Test all setup steps, error handling

3. **Upload System (5 routes)**
   - Basic test exists
   - **Recommendation:** Test file validation, size limits, file types

4. **Filter Endpoints (15 routes)**
   - Partial coverage
   - **Recommendation:** Test each filter type with various inputs

### Priority 3: Module-Specific Gaps

1. **Projects Module (9 routes)**
   - Very limited coverage
   - **Recommendation:** Comprehensive CRUD tests for projects and tasks

2. **Payments Module (9 routes)**
   - Partial coverage
   - **Recommendation:** Test payment creation, validation, gateway integration

3. **Tax Rates (4 routes)**
   - No dedicated test file
   - **Recommendation:** Create TaxRatesControllerTest.php

## Recommended Actions

### Immediate (This Week)
1. ✅ Create SessionsControllerTest.php
2. ✅ Enhance SetupControllerTest.php with all wizard steps
3. ✅ Add payment gateway callback tests

### Short Term (Next 2 Weeks)
1. ⚠️ Complete Projects module tests
2. ⚠️ Enhance Payments module tests
3. ⚠️ Create TaxRatesControllerTest.php
4. ⚠️ Improve Import system tests
5. ⚠️ Enhance Upload system tests

### Medium Term (Next Month)
1. Add negative test cases for all CRUD operations
2. Add authorization tests (user permissions)
3. Add integration tests for complex workflows
4. Add performance tests for high-volume operations

## Testing Guidelines

### For Each Route, Test:

1. **Happy Path**
   - Valid input produces expected output
   - Data is correctly saved/updated/deleted

2. **Validation**
   - Invalid input returns appropriate errors
   - Required fields are enforced
   - Data types are validated

3. **Authorization**
   - Unauthenticated users redirected to login
   - Insufficient permissions return 403
   - Users can only access their own data (where applicable)

4. **Edge Cases**
   - Empty results
   - Maximum values
   - Boundary conditions
   - Special characters

5. **Error Handling**
   - Database errors handled gracefully
   - File not found returns 404
   - Duplicate entries handled

### Test Pattern

```php
#[CoversClass(ControllerClass::class)]
class ControllerClassTest extends FeatureTestCase
{
    #[Test]
    public function it_performs_expected_action_when_conditions_met(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $data = ['key' => 'value'];
        
        /** Act */
        $this->actingAs($user);
        $response = $this->post(route('route.name'), $data);
        
        /** Assert */
        $response->assertOk();
        $response->assertViewHas('expected_key');
        $this->assertDatabaseHas('table', ['key' => 'value']);
    }
}
```

## Progress Tracking

### Overall Coverage

| Category | Routes | Tests | Coverage % | Status |
|----------|--------|-------|-----------|---------|
| Authentication | 5 | 0 | 0% | ❌ None |
| Core CRUD | 40 | 25 | 63% | ⚠️ Partial |
| AJAX Operations | 35 | 25 | 71% | ✅ Good |
| Guest Routes | 29 | 15 | 52% | ⚠️ Partial |
| Admin Features | 50 | 25 | 50% | ⚠️ Partial |
| Payments | 15 | 5 | 33% | ❌ Poor |
| Projects | 9 | 2 | 22% | ❌ Poor |
| **TOTAL** | **~212** | **~97** | **~46%** | **⚠️** |

### Weekly Goals

**Week 1:**
- [ ] Create SessionsControllerTest.php (5 tests)
- [ ] Enhance SetupControllerTest.php (+6 tests)
- [ ] Add gateway callback tests (+5 tests)
- **Target:** +16 tests, 53% coverage

**Week 2:**
- [ ] Complete Projects module tests (+15 tests)
- [ ] Enhance Payments module tests (+10 tests)
- **Target:** +25 tests, 65% coverage

**Week 3:**
- [ ] Create TaxRatesControllerTest.php (+6 tests)
- [ ] Improve Import system tests (+5 tests)
- [ ] Enhance Upload system tests (+7 tests)
- **Target:** +18 tests, 73% coverage

**Week 4:**
- [ ] Add negative test cases (+20 tests)
- [ ] Add authorization tests (+15 tests)
- **Target:** +35 tests, 85% coverage

## Route List Export

To generate a complete route list:

```bash
# List all route definitions
find Modules -name "*.php" -path "*/routes/*" -exec grep -h "Route::" {} \; | sort

# Count routes by HTTP method
find Modules -name "*.php" -path "*/routes/*" -exec grep -h "Route::" {} \; | \
  grep -oE "Route::(get|post|put|patch|delete)" | sort | uniq -c

# Count routes by module
for module in Core Invoices Quotes Crm Products Payments Projects; do
  count=$(find Modules/$module -name "*.php" -path "*/routes/*" -exec grep -c "Route::" {} + 2>/dev/null | awk '{sum+=$1} END {print sum}')
  echo "$module: $count routes"
done
```

## Notes

- Route counts are based on `Route::` method calls in route files
- Some routes may have multiple HTTP methods (GET + POST)
- Test coverage percentages are estimates based on existing test files
- Some AJAX routes may be tested indirectly through feature tests
- Guest routes need special attention for security testing
