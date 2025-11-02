# Code Standardization Guide

This document defines the unified coding standards for the InvoicePlane refactoring project.

## Table of Contents

1. [Controller Standards](#controller-standards)
2. [Service Standards](#service-standards)
3. [FormRequest Standards](#formrequest-standards)
4. [Test Standards](#test-standards)
5. [PHPDoc Standards](#phpdoc-standards)
6. [Route Standards](#route-standards)

## Controller Standards

### File Structure

Every controller must follow this structure:

```php
<?php

declare(strict_types=1);

namespace Modules\{ModuleName}\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\{ModuleName}\Http\Requests\{EntityName}Request;
use Modules\{ModuleName}\Models\{EntityName};
use Modules\{ModuleName}\Services\{EntityName}Service;

/**
 * {EntityName}Controller
 *
 * {Brief description of what this controller manages}
 *
 * @legacy-file application/modules/{legacy_module}/controllers/{LegacyController}.php
 */
class {EntityName}Controller
{
    /**
     * @param {EntityName}Service ${entityName}Service Service for {entity} business logic
     */
    public function __construct(
        private readonly {EntityName}Service ${entityName}Service
    ) {
    }

    // Methods follow...
}
```

### Key Principles

1. **Declare Strict Types**: Every file starts with `declare(strict_types=1);`
2. **Use Property Promotion**: Constructor parameters with visibility modifiers
3. **Readonly Properties**: Use `readonly` for dependency-injected services
4. **Type Hints**: Every method parameter and return value must have type hints
5. **NO Database Queries**: All database logic goes in services
6. **Early Returns**: Use early returns for guard clauses
7. **FormRequest Validation**: NEVER inline validation - always use FormRequest classes

### Standard CRUD Methods

Every resource controller should have these methods:

- `index(int $page = 0): View` - List resources with pagination
- `create(): View` - Show create form
- `store(EntityRequest $request): RedirectResponse` - Store new resource
- `show(Entity $entity): View` - Display single resource (route model binding)
- `edit(Entity $entity): View` - Show edit form (route model binding)
- `update(EntityRequest $request, Entity $entity): RedirectResponse` - Update resource
- `destroy(Entity $entity): RedirectResponse` - Delete resource

### Example: TasksController

See `Modules/Projects/Controllers/TasksController.php` for a complete reference implementation.

## Service Standards

### File Structure

```php
<?php

declare(strict_types=1);

namespace Modules\{ModuleName}\Services;

use Modules\Core\Services\BaseService;
use Modules\{ModuleName}\Models\{EntityName};

/**
 * {EntityName}Service
 *
 * Service class for {entity} business logic
 *
 * @legacy-file application/modules/{legacy_module}/models/Mdl_{entity}.php
 */
class {EntityName}Service extends BaseService
{
    /**
     * Get the model class managed by this service.
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return {EntityName}::class;
    }

    // Business logic methods...
}
```

### Key Principles

1. **Extend BaseService**: All services extend `Modules\Core\Services\BaseService`
2. **Declare Model Class**: Implement `getModelClass()` method
3. **Business Logic Only**: Services contain business logic, NOT validation
4. **Use BaseService Methods**: Leverage inherited CRUD methods
5. **Type Hints**: All methods must have complete type hints
6. **PHPDoc**: Document all public methods with legacy references

### Standard Service Methods

Services inherit from BaseService:
- `create(array $data): Model`
- `update(int $id, array $data): Model`
- `delete(int $id): bool`
- `find(int $id): ?Model`
- `findOrFail(int $id): Model`

Additional business logic methods as needed.

## FormRequest Standards

### File Structure

```php
<?php

declare(strict_types=1);

namespace Modules\{ModuleName}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * {EntityName}Request
 *
 * Form request validation for {entity} create and update operations
 *
 * @legacy-file application/modules/{legacy_module}/models/Mdl_{entity}.php (validation_rules)
 */
class {EntityName}Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // or implement authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string|array>
     */
    public function rules(): array
    {
        return [
            'field_name' => ['required', 'string', 'max:255'],
            'another_field' => ['nullable', 'integer', 'exists:ip_table,id'],
            // etc...
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'field_name.required' => trans('validation_field_name_required'),
            // etc...
        ];
    }
}
```

### Key Principles

1. **Array Syntax**: Use array syntax for validation rules `['required', 'string']`
2. **Shared Rules**: Same FormRequest for both create and update
3. **Type Hints**: Return type must be `array<string, string|array>`
4. **Custom Messages**: Use `messages()` for translations
5. **Legacy Reference**: Document which legacy validation this replaces

## Test Standards

### File Structure

```php
<?php

declare(strict_types=1);

namespace Modules\{ModuleName}\Tests\Feature;

use Modules\{ModuleName}\Controllers\{EntityName}Controller;
use Modules\{ModuleName}\Models\{EntityName};
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * {EntityName}Controller Feature Tests
 *
 * Comprehensive test suite covering all controller methods with
 * data providers for realistic test scenarios.
 */
#[CoversClass({EntityName}Controller::class)]
class {EntityName}ControllerTest extends FeatureTestCase
{
    #[Test]
    #[Group('smoke')]
    public function it_displays_list_of_{entities}(): void
    {
        /** Arrange */
        ${entity} = {EntityName}::factory()->create();

        /** Act */
        $this->actingAs($this->createUser());
        $response = $this->get(route('{entities}.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('{module}::{view_name}');
        $response->assertViewHas('{entities}');
        
        // Test ACTUAL DATA, not just status
        ${entities} = $response->viewData('{entities}');
        $this->assertGreaterThan(0, ${entities}->count());
        $this->assertEquals(${entity}->id, ${entities}->first()->id);
    }
    
    // More tests...
}
```

### Key Principles

1. **CoversClass Attribute**: Every test class must have `#[CoversClass(ControllerClass::class)]`
2. **Test Attribute**: Use `#[Test]` instead of `test` prefix
3. **Group Attributes**: Organize with `#[Group('smoke')]`, `#[Group('crud')]`, etc.
4. **AAA Pattern**: Arrange, Act, Assert comments in every test
5. **Data Providers**: Use `#[DataProvider('methodName')]` for realistic test data
6. **Test Data, Not Status**: Assert on actual data returned, not just HTTP 200
7. **Authentication**: Split `actingAs()` from HTTP method calls on separate lines
8. **Descriptive Names**: Test method names like `it_displays_list_of_tasks`

### Test Groups

- `smoke`: Basic functionality tests
- `crud`: Create, read, update, delete operations
- `validation`: Validation rule tests
- `edge-cases`: Edge case and error handling
- `authentication`: Authentication/authorization tests
- `integration`: Integration tests

### Data Provider Example

```php
/**
 * @return array<string, array{taskName: string, status: int, expectsError: bool}>
 */
public static function taskCreationProvider(): array
{
    return [
        'valid task with all fields' => [
            'taskName' => 'Complete Project',
            'status' => 1,
            'expectsError' => false,
        ],
        'empty task name fails' => [
            'taskName' => '',
            'status' => 1,
            'expectsError' => true,
        ],
        'invalid status fails' => [
            'taskName' => 'Task',
            'status' => 999,
            'expectsError' => true,
        ],
    ];
}

#[Test]
#[DataProvider('taskCreationProvider')]
public function it_validates_task_creation(string $taskName, int $status, bool $expectsError): void
{
    // Test implementation...
}
```

## PHPDoc Standards

### Controller Method Documentation

```php
/**
 * Display a paginated list of {entities}.
 *
 * Fetches {entities} from the database with pagination and displays them
 * in the index view along with filter configuration.
 *
 * @param int $page Page number for pagination (default: 0)
 *
 * @return View
 *
 * @legacy-function index
 * @legacy-file application/modules/{legacy_module}/controllers/{LegacyController}.php
 * @legacy-line 42 (optional)
 */
```

### Service Method Documentation

```php
/**
 * Calculate the total amount for a {entity}.
 *
 * Sums up all item amounts, applies discounts, and calculates tax.
 *
 * @param int $entityId The {entity} ID
 *
 * @return float The calculated total amount
 *
 * @legacy-function calculate_total
 * @legacy-file application/modules/{legacy_module}/models/Mdl_{entity}.php
 * @legacy-line 156 (optional)
 */
```

### Required Tags

1. **Brief Description**: One-line summary
2. **Detailed Description**: What the method does (optional but recommended)
3. **@param**: For each parameter with type and description
4. **@return**: Return type and description
5. **@legacy-function**: Original function name from legacy code
6. **@legacy-file**: Full path to legacy file
7. **@legacy-line**: Line number in legacy file (optional)
8. **@throws**: If method can throw exceptions (optional)

## Route Standards

### Route File Organization

Each module should have route files in `Modules/{Module}/Routes/web/`:

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\{Module}\Controllers\{EntityName}Controller;

Route::middleware(['web', 'auth'])->group(function () {
    // Resource routes
    Route::get('{entities}', [{EntityName}Controller::class, 'index'])->name('{entities}.index');
    Route::get('{entities}/create', [{EntityName}Controller::class, 'create'])->name('{entities}.create');
    Route::post('{entities}', [{EntityName}Controller::class, 'store'])->name('{entities}.store');
    Route::get('{entities}/{{entity}}', [{EntityName}Controller::class, 'show'])->name('{entities}.show');
    Route::get('{entities}/{{entity}}/edit', [{EntityName}Controller::class, 'edit'])->name('{entities}.edit');
    Route::put('{entities}/{{entity}}', [{EntityName}Controller::class, 'update'])->name('{entities}.update');
    Route::delete('{entities}/{{entity}}', [{EntityName}Controller::class, 'destroy'])->name('{entities}.destroy');
});
```

### Public Routes

Only these routes should be without `auth` middleware:
- `/` - Welcome page
- `/setup` - Installation setup
- `/sessions/login` - Login page
- `/sessions/passwordreset` - Password reset
- `/guest/*` - Guest access routes (invoices, quotes with URL keys)

### Route Naming

- Use dot notation: `{entities}.index`, `{entities}.create`, etc.
- Plural for resource routes: `tasks.index`, `projects.show`
- Descriptive for custom routes: `invoices.mark-sent`, `quotes.convert-to-invoice`

## Migration Checklist

When refactoring a controller, ensure:

- [ ] Add `declare(strict_types=1);` at the top
- [ ] Use constructor property promotion with `readonly`
- [ ] Add complete PHPDoc blocks with `@legacy-*` tags
- [ ] Move all validation to FormRequest classes
- [ ] Move all database queries to Service methods
- [ ] Use route model binding where applicable
- [ ] Add comprehensive tests with `#[CoversClass()]`
- [ ] Use data providers for realistic test data
- [ ] Test actual data, not just HTTP status codes
- [ ] Add authentication middleware to routes
- [ ] Follow early return pattern for guard clauses
- [ ] Organize use statements alphabetically
- [ ] Remove any `AllowDynamicProperties` attributes
- [ ] Remove extends from legacy base controllers (AdminController, etc.)

## Reference Implementation

The following files serve as reference implementations:

1. **Controller**: `Modules/Projects/Controllers/TasksController.php`
2. **Service**: `Modules/Projects/Services/TaskService.php`
3. **FormRequest**: `Modules/Projects/Http/Requests/TaskRequest.php`
4. **Tests**: `Modules/Projects/Tests/Feature/TasksControllerTest.php`

## Tools and Scripts

### Validation Script

Run this to check compliance:

```bash
composer dump-autoload
composer check
```

### Test Execution

```bash
php artisan test --filter={EntityName}ControllerTest
```

## Next Steps

1. Use this guide as a reference for all refactoring work
2. Refactor one controller at a time
3. Create tests before refactoring (TDD approach)
4. Run tests after each change
5. Document all legacy references
6. Update this guide as patterns evolve
