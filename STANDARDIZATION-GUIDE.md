# Code Standardization Guide

This document defines the unified coding standards for the InvoicePlane refactoring project.

## Table of Contents

1. [Controller Standards](#controller-standards)
2. [Service Standards](#service-standards)
3. [FormRequest Standards](#formrequest-standards)
4. [Test Standards](#test-standards)
5. [PHPDoc Standards](#phpdoc-standards)

## Controller Standards

### File Structure

Every controller must follow this structure:

```php
<?php

namespace Modules\{ModuleName}\Controllers;

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
    protected {EntityName}Service ${entityName}Service;

    public function __construct({EntityName}Service ${entityName}Service)
    {
        $this->{entityName}Service = ${entityName}Service;
    }

    // Methods follow...
}
```

### Key Principles

1. **NO strict types declaration**: Do NOT use `declare(strict_types=1);`
2. **Standard Constructor**: Use traditional constructor pattern, NOT property promotion
3. **NO readonly**: Do NOT use `readonly` keyword
4. **Type Hints**: Use type hints where helpful, but don't overdo it
5. **NO Database Queries**: All database logic goes in services
6. **Early Returns**: Use early returns for guard clauses
7. **FormRequest Validation**: Use FormRequest classes for validation when appropriate

### Standard Controller Method Pattern: form()

Controllers should use the **`form()` method pattern** for create/edit operations, NOT separate REST methods:

```php
/**
 * Display form for creating or editing an entity.
 *
 * @param int|null $id Entity ID (null for create)
 *
 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
 */
public function form(?int $id = null)
{
    // Handle cancel button
    if (request()->post('btn_cancel')) {
        return redirect()->route('entities.index');
    }

    // Handle form submission
    if (request()->isMethod('post') && request()->post('btn_submit')) {
        $validated = request()->validate([
            // validation rules
        ]);

        if ($id) {
            $this->entityService->update($id, $validated);
        } else {
            $this->entityService->create($validated);
        }

        return redirect()->route('entities.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    // Load existing record for editing or create new
    if ($id) {
        $entity = $this->entityService->find($id);
        if (!$entity) {
            abort(404);
        }
    } else {
        $entity = new Entity();
    }

    return view('entities_form', compact('entity'));
}
```

### Example Controllers

See these controllers for reference implementations:
- `Modules/Projects/Controllers/TasksController.php`
- `Modules/Products/Controllers/UnitsController.php`
- `Modules/Products/Controllers/FamiliesController.php`

## Service Standards

### File Structure

```php
<?php

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

1. **NO strict types**: Do NOT use `declare(strict_types=1);`
2. **Extend BaseService**: All services extend `Modules\Core\Services\BaseService`
3. **Declare Model Class**: Implement `getModelClass()` method
4. **Business Logic Only**: Services contain business logic, NOT validation
5. **Use BaseService Methods**: Leverage inherited CRUD methods
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

1. **NO strict types**: Do NOT use `declare(strict_types=1);` in test files
2. **CoversClass Attribute**: Every test class must have `#[CoversClass(ControllerClass::class)]`
3. **Test Attribute**: Use `#[Test]` instead of `test` prefix
4. **Group Attributes**: Organize with `#[Group('smoke')]`, `#[Group('crud')]`, etc.
5. **AAA Pattern**: Arrange, Act, Assert comments in every test
6. **Data Providers**: Use `#[DataProvider('methodName')]` for realistic test data
7. **Test Data, Not Status**: Assert on actual data returned, not just HTTP 200
8. **Authentication**: Split `actingAs()` from HTTP method calls on separate lines
9. **Descriptive Names**: Test method names like `it_displays_list_of_tasks`

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
 * @return \Illuminate\View\View
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

## Migration Checklist

When refactoring a controller, ensure:

- [ ] Add complete PHPDoc blocks with `@legacy-*` tags
- [ ] Move all validation to FormRequest classes (when appropriate)
- [ ] Move all database queries to Service methods
- [ ] Add comprehensive tests with `#[CoversClass()]`
- [ ] Use data providers for realistic test data
- [ ] Test actual data, not just HTTP status codes
- [ ] Follow early return pattern for guard clauses
- [ ] Organize use statements alphabetically
- [ ] Remove any `AllowDynamicProperties` attributes
- [ ] Use the `form()` method pattern for create/edit operations

## Reference Implementation

The following files serve as reference implementations:

1. **Controller**: `Modules/Projects/Controllers/TasksController.php`
2. **Controller with form()**: `Modules/Products/Controllers/FamiliesController.php`
3. **Service**: `Modules/Projects/Services/TaskService.php`
4. **FormRequest**: `Modules/Projects/Http/Requests/TaskRequest.php`
5. **Tests**: `Modules/Projects/Tests/Feature/TasksControllerTest.php`

## Next Steps

1. Use this guide as a reference for all refactoring work
2. Refactor one controller at a time
3. Create tests before refactoring (TDD approach)
4. Run tests after each change
5. Document all legacy references
6. Update this guide as patterns evolve
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
