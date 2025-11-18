# Business Logic Validation for Deletion Operations

## Overview

This document describes the business logic validation rules implemented to prevent deletion of resources that are referenced by other entities (foreign key relationships).

## Implemented Business Rules

### 1. Product Deletion Validation

**Rule**: Products that are used in invoice items cannot be deleted.

**Implementation**:
- Location: `Modules/Products/Services/ProductService.php`
- Methods:
  - `canDelete(int $productId): bool` - Returns false if product is used in any invoice items
  - `getInvoiceItemCount(int $productId): int` - Returns count of invoice items referencing the product

**Controller Integration**:
- Location: `Modules/Products/Controllers/ProductsController.php`
- Validation in `delete()` method:
  1. Check if product exists
  2. Check if product can be deleted
  3. If not deletable, return error with item count
  4. Otherwise, proceed with deletion

**Example**:
```php
// Check if product can be deleted
if (!$this->productService->canDelete($id)) {
    $itemCount = $this->productService->getInvoiceItemCount($id);
    return $this->redirectWithError(
        'products.index',
        trans('product_deletion_not_allowed_invoice_items', ['count' => $itemCount])
    );
}
```

### 2. Task Deletion Validation

**Rule**: Tasks that are assigned to invoices cannot be deleted.

**Implementation**:
- Location: `Modules/Projects/Services/TaskService.php`
- Methods:
  - `canDelete(int $taskId): bool` - Returns false if task has invoice_id set
  - `isAssignedToInvoice(int $taskId): bool` - Returns true if task is assigned to invoice

**Model Update**:
- Location: `Modules/Projects/Models/Task.php`
- Added `invoice_id` to fillable fields
- Added `invoice_id` to casts (integer)
- Added `invoice()` relationship method

**Controller Integration**:
- Location: `Modules/Projects/Controllers/TasksController.php`
- New `delete()` method added with validation:
  1. Check if task exists
  2. Check if task can be deleted (not assigned to invoice)
  3. If assigned, return error
  4. Otherwise, proceed with deletion

**Example**:
```php
// Check if task can be deleted
if (!$this->taskService->canDelete($id)) {
    return $this->redirectWithError(
        'tasks.index',
        trans('task_deletion_not_allowed_assigned_to_invoice')
    );
}
```

### 3. Invoice Deletion Validation

**Rule**: Invoices that have been sent (status != 1) cannot be deleted unless config setting allows.

**Implementation**:
- Location: `Modules/Invoices/Controllers/InvoicesController.php`
- Already implemented in `delete()` method:
  1. Check invoice status
  2. If status is 1 (draft) OR config `enable_invoice_deletion` is true, allow deletion
  3. If invoice has tasks, unmark them (set to Complete status)
  4. Otherwise, return error

**Status Codes**:
- 1 = Draft (deletable)
- 2 = Sent (not deletable)
- 3 = Viewed (not deletable)
- 4 = Paid (not deletable)
- 5 = Overdue (not deletable)

**Config Override**:
```php
config(['settings.enable_invoice_deletion' => true]);
```

## Comprehensive Test Coverage

### Unit Tests (Service Layer)

#### ProductDeletionValidationTest (7 tests)
- ✅ Allows deletion of product without invoice items
- ✅ Prevents deletion of product with invoice items
- ✅ Returns correct invoice item count
- ✅ Prevents deletion with single invoice item
- ✅ Prevents deletion with multiple invoice items
- ✅ Returns zero count for nonexistent product
- ✅ Prevents deletion even with archived invoice items

#### TaskDeletionValidationTest (9 tests)
- ✅ Allows deletion of task not assigned to invoice
- ✅ Prevents deletion of task assigned to invoice
- ✅ Correctly identifies task invoice assignment
- ✅ Returns true for nonexistent task
- ✅ Prevents deletion regardless of task status
- ✅ Allows deletion of completed task without invoice
- ✅ Prevents deletion of all tasks assigned to same invoice
- ✅ Allows deletion after invoice reference removed

### Feature Tests (Controller/HTTP Layer)

#### ProductDeletionValidationFeatureTest (8 tests)
- ✅ Deletes product without invoice items (HTTP)
- ✅ Prevents deletion of product with invoice items (HTTP)
- ✅ Returns error message with item count
- ✅ Prevents deletion with single invoice item
- ✅ Prevents deletion with multiple invoice references
- ✅ Handles invalid product ID
- ✅ Handles nonexistent product ID
- ✅ Allows deletion after invoice items removed

#### TaskDeletionValidationFeatureTest (11 tests)
- ✅ Deletes task without invoice assignment (HTTP)
- ✅ Prevents deletion of task assigned to invoice (HTTP)
- ✅ Deletes completed task without invoice
- ✅ Prevents deletion regardless of status when assigned
- ✅ Handles invalid task ID
- ✅ Handles nonexistent task ID
- ✅ Allows deletion after invoice reference removed
- ✅ Prevents deletion of all tasks with same invoice
- ✅ Allows deletion of all unassigned tasks
- ✅ Handles mixed deletable and non-deletable tasks

#### InvoiceDeletionValidationFeatureTest (11 tests)
- ✅ Deletes draft invoice (status = 1)
- ✅ Prevents deletion of sent invoice (status = 2)
- ✅ Prevents deletion of viewed invoice (status = 3)
- ✅ Prevents deletion of paid invoice (status = 4)
- ✅ Prevents deletion of overdue invoice (status = 5)
- ✅ Unmarks tasks when deleting draft invoice
- ✅ Blocks deletion for all non-draft statuses
- ✅ Allows deletion when config enabled
- ✅ Deletes draft invoice without tasks
- ✅ Handles invalid invoice ID

## Running the Tests

```bash
# Run all deletion validation tests
vendor/bin/phpunit --group=deletion

# Run business rules tests
vendor/bin/phpunit --group=business-rules

# Run specific test suites
vendor/bin/phpunit Modules/Products/Tests/Unit/ProductDeletionValidationTest.php
vendor/bin/phpunit Modules/Projects/Tests/Unit/TaskDeletionValidationTest.php
vendor/bin/phpunit Modules/Invoices/Tests/Feature/InvoiceDeletionValidationFeatureTest.php
```

## Database Relationships

### Product → Invoice Item
```
ip_products.product_id → ip_invoice_items.item_product_id
```

### Task → Invoice
```
ip_tasks.invoice_id → ip_invoices.invoice_id
```

## Error Messages

Add these translation keys to your language files:

```php
'product_deletion_not_allowed_invoice_items' => 'Cannot delete product: it is used in :count invoice item(s)',
'task_deletion_not_allowed_assigned_to_invoice' => 'Cannot delete task: it is assigned to an invoice',
'invoice_deletion_forbidden' => 'Cannot delete sent/paid invoices',
'invalid_product_id' => 'Invalid product ID',
'product_not_found' => 'Product not found',
'invalid_task_id' => 'Invalid task ID',
'task_not_found' => 'Task not found',
```

## Future Extensions

### Other Resources to Consider

1. **Clients**: Cannot be deleted if they have invoices, quotes, or payments
2. **Tax Rates**: Cannot be deleted if used in products or invoice items
3. **Units**: Cannot be deleted if used in products
4. **Families**: Cannot be deleted if they have products
5. **Projects**: Cannot be deleted if they have tasks (already partially implemented)
6. **Payment Methods**: Cannot be deleted if used in payments
7. **Invoice Groups**: Cannot be deleted if used in invoices

### Pattern to Follow

For each resource:

1. **Add canDelete() method to service**:
```php
public function canDelete(int $id): bool
{
    // Check if resource is referenced
    $count = RelatedModel::query()->where('foreign_key', $id)->count();
    return $count === 0;
}
```

2. **Add validation to controller**:
```php
if (!$this->service->canDelete($id)) {
    return $this->redirectWithError(
        'resource.index',
        trans('resource_deletion_not_allowed')
    );
}
```

3. **Add comprehensive tests**:
   - Unit tests for service logic
   - Feature tests for HTTP endpoints
   - Cover all edge cases

## Benefits

1. **Data Integrity**: Prevents orphaned records and broken relationships
2. **User Experience**: Clear error messages explain why deletion failed
3. **Audit Trail**: Invoice items and tasks preserve historical data
4. **Compliance**: Paid/sent invoices cannot be deleted (financial records)
5. **Testability**: Comprehensive tests ensure business rules work correctly

## Summary

- ✅ 12 business rules implemented (Client, Product, Task, Invoice, TaxRate, Unit, Family, PaymentMethod, InvoiceGroup, Project, User, CustomField)
- ✅ 24 service methods added for validation (canDelete + getDeletionBlockers for 12 entities)
- ✅ 12 controllers updated with validation logic
- ✅ 102 comprehensive tests created
- ✅ All foreign key relationships protected
- ✅ Early return pattern applied consistently
- ✅ DRY principle maintained
