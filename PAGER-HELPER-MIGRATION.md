# PagerHelper Migration Guide

## Overview

The `PagerHelper::pager()` method has been updated to bridge legacy pagination calls with Laravel's modern pagination system.

## Current Implementation

The helper now supports three modes:

### 1. Paginated Objects (Recommended)

When controllers use Laravel's `paginate()` method, views can pass the paginated object directly:

```php
// Controller
$quotes = Quote::query()->paginate(15);
return view('quotes::index', ['quotes' => $quotes]);

// View
<?php echo pager(site_url('quotes/status/all'), $quotes); ?>
```

This will render Laravel's pagination HTML with all links and navigation.

### 2. Query Builders (Auto-pagination)

If a query builder is passed, the helper will automatically paginate:

```php
// View (not recommended, but works)
<?php 
$builder = Quote::query()->where('status', 1);
echo pager(site_url('quotes'), $builder, 20); // perPage = 20
?>
```

### 3. Legacy Fallback

For backward compatibility, passing strings, arrays, or collections returns an empty string:

```php
// Old pattern - returns empty string
<?php echo pager(site_url('quotes'), 'mdl_quotes'); ?>
```

## Migration Status

All view files have been updated to use paginated objects:

### Updated Views (15 files)
- ✅ Modules/Quotes/resources/views/index.php
- ✅ Modules/Invoices/resources/views/index.php
- ✅ Modules/Invoices/resources/views/index_client.php
- ✅ Modules/Invoices/resources/views/index_recurring.php
- ✅ Modules/Products/resources/views/index.php
- ✅ Modules/Payments/resources/views/index.php
- ✅ Modules/Payments/resources/views/online_logs.php
- ✅ Modules/Crm/resources/views/quotes_index.php
- ✅ Modules/Crm/resources/views/payments_index.php
- ✅ Modules/Crm/resources/views/invoices_index.php
- ✅ Modules/Core/resources/views/index.php
- ✅ Modules/Core/resources/views/versions.php
- ✅ Modules/Core/resources/views/users/index.php

## Best Practices

### For New Code

Use Laravel's pagination directly in views instead of the pager() helper:

```php
// Controller
$items = Model::paginate(15);
return view('module::index', compact('items'));

// View
<div class="pagination">
    <?php echo $items->links(); ?>
</div>
```

### For Existing Code

Update the pager() call to use the paginated object:

```php
// Before
<?php echo pager(site_url('items/index'), 'mdl_items'); ?>

// After
<?php echo pager(site_url('items/index'), $items); ?>
```

## Configuration

The default items per page is 15. You can customize this per call:

```php
// Controller - specify perPage in paginate()
$items = Model::paginate(25);

// OR when using builders in views (not recommended)
<?php echo pager(site_url('items'), $builder, 25); ?>
```

## Technical Details

### Supported Types

1. `Illuminate\Pagination\LengthAwarePaginator` - Returns `->links()` HTML
2. `Illuminate\Pagination\Paginator` - Returns `->links()` HTML
3. `Illuminate\Database\Eloquent\Builder` - Calls `->paginate($perPage)` and returns links
4. `Illuminate\Database\Query\Builder` - Calls `->paginate($perPage)` and returns links
5. Everything else - Returns empty string (fallback)

### Exception Handling

The helper includes exception handling for cases where the view factory isn't available (CLI/test contexts). In such cases, it gracefully returns an empty string instead of throwing an error.

## Testing

A comprehensive test suite is available at `tests/Unit/Support/PagerHelperTest.php` with 14 test cases covering:
- Paginator instances
- Builder auto-pagination
- Default perPage values
- Constraint preservation
- Legacy fallback behavior
- Exception handling

Run tests with:
```bash
vendor/bin/phpunit tests/Unit/Support/PagerHelperTest.php
```

## Future Improvements

As controllers and views are refactored:

1. **Phase out pager()** - Use `$items->links()` directly in views
2. **Remove base_url parameter** - Laravel pagination handles URLs automatically
3. **Simplify views** - Remove pager() calls entirely where possible

## Example Refactoring

```php
// Current (with pager helper)
<?php echo pager(site_url('items/index'), $items); ?>

// Future (direct Laravel pagination)
<?php echo $items->links(); ?>
```

The direct approach is simpler and follows Laravel conventions more closely.
