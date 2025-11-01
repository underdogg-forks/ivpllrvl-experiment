# View Refactoring Guide

## Overview
This document explains the refactoring done to remove direct Eloquent model and service calls from view files, ensuring proper MVC separation where controllers prepare and pass all data to views.

## Refactored Views

### 1. Custom Field Usage List Views

**Files:**
- `Modules/Core/resources/views/custom_field_usage_list.php`
- `Modules/Core/resources/views/partial/custom_field_usage_list.php`

**Previous Behavior:**
Views directly called `Invoice::find()` and `Quote::find()` to fetch invoice/quote numbers.

```php
// OLD - Direct model calls in view
if ($what === 'invoice') {
    $record = \Modules\Invoices\Models\Invoice::find($id);
    $fid = $record ? '#' . $record->invoice_number : $id;
}
```

**New Behavior:**
Views now accept pre-fetched display values as a parameter.

```php
// NEW - Data passed from controller
if (isset($custom_field_usage_display_values) && isset($custom_field_usage_display_values[$id])) {
    $display = $custom_field_usage_display_values[$id];
} else {
    $display = $id; // Fallback
}
```

**Controller Implementation Example:**

If you need to use these views, the controller should:

```php
public function field(int $id)
{
    $field = CustomField::findOrFail($id);
    $custom_field_usage = /* fetch usage data */;
    
    // Determine the type (invoice, quote, etc.)
    $what = strtr($field->custom_field_table, ['ip_' => '', '_custom' => '']);
    
    // Pre-fetch display values
    $custom_field_usage_display_values = [];
    
    if ($what === 'invoice') {
        $invoiceIds = $custom_field_usage->pluck('invoice_id');
        $invoices = \Modules\Invoices\Models\Invoice::whereIn('invoice_id', $invoiceIds)
            ->get()
            ->pluck('invoice_number', 'invoice_id')
            ->map(fn($num) => '#' . $num)
            ->toArray();
        $custom_field_usage_display_values = $invoices;
    } elseif ($what === 'quote') {
        $quoteIds = $custom_field_usage->pluck('quote_id');
        $quotes = \Modules\Quotes\Models\Quote::whereIn('quote_id', $quoteIds)
            ->get()
            ->pluck('quote_number', 'quote_id')
            ->map(fn($num) => '#' . $num)
            ->toArray();
        $custom_field_usage_display_values = $quotes;
    }
    
    return view('core::field', [
        'field' => $field,
        'custom_field_usage' => $custom_field_usage,
        'custom_field_usage_display_values' => $custom_field_usage_display_values,
    ]);
}
```

### 2. Settings Invoice Partial View

**File:**
- `Modules/Core/resources/views/partial_settings_invoices.php`

**Previous Behavior:**
View directly accessed Sumex class constants.

```php
// OLD - Direct class constant access in view
$roles = Sumex::ROLES;
$places = Sumex::PLACES;
$cantons = Sumex::CANTONS;
```

**New Behavior:**
View now expects these constants to be passed as variables.

```php
// NEW - Data passed from controller
$roles = $sumex_roles ?? [];
$places = $sumex_places ?? [];
$cantons = $sumex_cantons ?? [];
```

**Controller Implementation Example:**

When creating a settings view that includes this partial:

```php
use Modules\Core\Libraries\Sumex;

public function index(): \Illuminate\View\View
{
    $settings = Setting::query()->get()->pluck('setting_value', 'setting_key')->toArray();
    
    // Prepare Sumex constants for views
    $sumex_roles = Sumex::ROLES;
    $sumex_places = Sumex::PLACES;
    $sumex_cantons = Sumex::CANTONS;
    
    // Prepare other data needed by partial views
    $invoice_groups = /* fetch invoice groups */;
    $payment_methods = /* fetch payment methods */;
    $pdf_invoice_templates = /* fetch PDF templates */;
    $public_invoice_templates = /* fetch public templates */;
    $email_templates_invoice = /* fetch email templates */;
    
    return view('core::settings_index', [
        'settings' => $settings,
        'sumex_roles' => $sumex_roles,
        'sumex_places' => $sumex_places,
        'sumex_cantons' => $sumex_cantons,
        'invoice_groups' => $invoice_groups,
        'payment_methods' => $payment_methods,
        'pdf_invoice_templates' => $pdf_invoice_templates,
        'public_invoice_templates' => $public_invoice_templates,
        'email_templates_invoice' => $email_templates_invoice,
    ]);
}
```

## Benefits

1. **Proper MVC Separation**: Controllers handle data fetching, views only display
2. **Better Performance**: Ability to optimize queries (e.g., batch loading, eager loading)
3. **Easier Testing**: Controllers can be tested independently of views
4. **More Maintainable**: Clear separation of concerns
5. **Better Caching**: Controller-prepared data can be cached more easily

## Backward Compatibility

All refactored views include fallback behavior:
- `custom_field_usage_list.php` falls back to displaying IDs if display values aren't provided
- `partial_settings_invoices.php` uses `?? []` to handle missing data gracefully

This ensures views won't break if controllers don't immediately pass the new parameters.

## Migration Checklist

When updating a controller to use these views:

- [ ] Identify what data the view needs
- [ ] Fetch data in controller using Eloquent models/services
- [ ] Pass data as named parameters to view
- [ ] Remove any direct model/service calls from view template
- [ ] Test that view renders correctly with new data
- [ ] Consider performance optimizations (eager loading, caching)

## Related Files

- Controllers that may use these views:
  - `Modules/Core/Controllers/CustomFieldsController.php`
  - `Modules/Core/Controllers/CustomValuesController.php`
  - `Modules/Core/Controllers/SettingsController.php`

- Related models:
  - `Modules/Invoices/Models/Invoice.php`
  - `Modules/Quotes/Models/Quote.php`
  - `Modules/Core/Libraries/Sumex.php`
