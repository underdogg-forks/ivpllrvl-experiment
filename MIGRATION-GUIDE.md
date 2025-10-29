# Migration Guide for Contributors

This guide helps you continue migrating CodeIgniter modules to the new PSR-4/Eloquent structure.

## Overview

We are migrating from CodeIgniter 3 HMVC (in `application/modules/`) to modern PSR-4 modules (in `Modules/`) using Illuminate components.

## Quick Reference

### Directory Mapping

| Old (CodeIgniter) | New (PSR-4) |
|------------------|-------------|
| `application/modules/invoices/models/Mdl_invoices.php` | `Modules/Invoices/Entities/Invoice.php` |
| `application/modules/invoices/controllers/Invoices.php` | `Modules/Invoices/Http/Controllers/InvoiceController.php` |
| `application/modules/invoices/views/index.php` | `Modules/Invoices/Resources/views/index.php` |

### Module Assignment

Legacy modules are distributed across new modules:

| Legacy Module | New Module |
|--------------|------------|
| dashboard, layout, settings, setup | Core |
| invoices, invoice_groups | Invoices |
| payments, payment_methods | Payments |
| products, families, units, tax_rates | Products |
| quotes | Quotes |
| clients, user_clients, projects, tasks | Crm |
| users, sessions | Users |
| custom_fields, custom_values | Custom |

## Step-by-Step Migration Process

### 1. Migrating a Model

**Example: Migrating `Mdl_items.php` to `InvoiceItem.php`**

#### Old Code (application/modules/invoices/models/Mdl_items.php):
```php
<?php
class Mdl_Items extends Response_Model
{
    public $table = 'ip_invoice_items';
    public $primary_key = 'ip_invoice_items.item_id';
    
    public function get_by_invoice($invoice_id)
    {
        $this->db->where('invoice_id', $invoice_id);
        return $this->db->get($this->table)->result();
    }
}
```

#### New Code (Modules/Invoices/Entities/InvoiceItem.php):
```php
<?php

namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

class InvoiceItem extends BaseModel
{
    protected $table = 'ip_invoice_items';
    protected $primaryKey = 'item_id';
    public $timestamps = false;
    
    protected $fillable = [
        'invoice_id',
        'item_name',
        'item_description',
        'item_quantity',
        'item_price',
        // ... other fields
    ];
    
    protected $casts = [
        'item_id' => 'integer',
        'invoice_id' => 'integer',
        'item_quantity' => 'decimal:2',
        'item_price' => 'decimal:2',
    ];
    
    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    
    public function product()
    {
        return $this->belongsTo('Modules\Products\Entities\Product', 'item_product_id');
    }
    
    // Scopes
    public function scopeByInvoice($query, int $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }
}
```

#### Usage Change:
```php
// OLD
$this->load->model('invoices/mdl_items');
$items = $this->mdl_items->get_by_invoice($invoice_id);

// NEW
use Modules\Invoices\Entities\InvoiceItem;

$items = InvoiceItem::byInvoice($invoice_id)->get();
// or
$invoice = Invoice::with('items')->find($invoice_id);
$items = $invoice->items;
```

### 2. Migrating a Controller

**Example: Migrating Invoices Controller**

#### Old Code (application/modules/invoices/controllers/Invoices.php):
```php
<?php
class Invoices extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_items');
    }
    
    public function index()
    {
        $data = [
            'invoices' => $this->mdl_invoices->get()->result()
        ];
        $this->load->view('invoice_index', $data);
    }
    
    public function view($id)
    {
        $data = [
            'invoice' => $this->mdl_invoices->get_by_id($id),
            'items' => $this->mdl_items->where('invoice_id', $id)->get()->result()
        ];
        $this->load->view('invoice_view', $data);
    }
}
```

#### New Code (Modules/Invoices/Http/Controllers/InvoiceController.php):
```php
<?php

namespace Modules\Invoices\Http\Controllers;

use Modules\Invoices\Entities\Invoice;

class InvoiceController
{
    public function index()
    {
        $invoices = Invoice::with('client')->get();
        return view('invoices::invoice_index', compact('invoices'));
    }
    
    public function view(int $id)
    {
        $invoice = Invoice::with(['client', 'items'])->findOrFail($id);
        return view('invoices::invoice_view', compact('invoice'));
    }
}
```

### 3. Migrating Views

Views remain as plain PHP but location changes:

**Old:** `application/modules/invoices/views/invoice_index.php`
**New:** `Modules/Invoices/Resources/views/invoice_index.php`

**Changes in view loading:**
```php
// OLD
$this->load->view('invoice_index', $data);

// NEW  
return view('invoices::invoice_index', $data);
// The 'invoices::' prefix refers to the module name
```

View file content remains mostly the same (plain PHP):
```php
<!-- Modules/Invoices/Resources/views/invoice_index.php -->
<h1>Invoices</h1>
<?php foreach ($invoices as $invoice): ?>
    <div>
        <?php echo $invoice->invoice_number; ?>
        <?php echo $invoice->client->client_name; ?>
    </div>
<?php endforeach; ?>
```

## Common Patterns

### Database Query Conversions

| CodeIgniter | Eloquent |
|------------|----------|
| `$this->db->get('table')->result()` | `Model::all()` |
| `$this->db->where('id', $id)->get('table')->row()` | `Model::find($id)` |
| `$this->db->where('field', 'value')->get('table')->result()` | `Model::where('field', 'value')->get()` |
| `$this->db->join('table2', 'condition')` | `Model::with('relation')` or `Model::join()` |
| `$this->db->insert('table', $data)` | `Model::create($data)` |
| `$this->db->where('id', $id)->update('table', $data)` | `Model::find($id)->update($data)` |
| `$this->db->where('id', $id)->delete('table')` | `Model::find($id)->delete()` |

### Relationship Patterns

```php
// One-to-Many (Invoice has many Items)
class Invoice extends BaseModel
{
    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }
}

// Many-to-One (Item belongs to Invoice)
class InvoiceItem extends BaseModel
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}

// Usage:
$invoice = Invoice::with('items')->find($id);
$items = $invoice->items; // Collection of items

$item = InvoiceItem::with('invoice')->find($id);
$invoice = $item->invoice; // Invoice object
```

### Scope Patterns

Replace custom query methods with scopes:

```php
// In Model
public function scopeActive($query)
{
    return $query->where('status', 1);
}

public function scopeByClient($query, int $clientId)
{
    return $query->where('client_id', $clientId);
}

// Usage
$invoices = Invoice::active()->byClient($clientId)->get();
```

## Checklist for Migrating a Module

- [ ] **Identify the target module** (Core, Invoices, Payments, etc.)
- [ ] **List all models** in the legacy module
- [ ] **Create Eloquent models** in `Modules/{Module}/Entities/`
  - [ ] Define table name
  - [ ] Define primary key
  - [ ] Define fillable fields
  - [ ] Define relationships
  - [ ] Convert custom methods to scopes
- [ ] **List all controllers** in the legacy module
- [ ] **Create new controllers** in `Modules/{Module}/Http/Controllers/`
  - [ ] Use dependency injection instead of $this->load
  - [ ] Use Eloquent models
  - [ ] Return views with new syntax
- [ ] **Move views** to `Modules/{Module}/Resources/views/`
  - [ ] Update view loading syntax
  - [ ] Update object property access if needed
- [ ] **Test the migrated functionality**
- [ ] **Document any issues or special cases**

## Tools and Commands

### Check PSR-12 Compliance
```bash
composer pint --test Modules/
```

### Format Code
```bash
composer pint Modules/
```

### Modernize Code
```bash
composer rector
```

## Tips and Best Practices

1. **One module at a time**: Don't try to migrate everything at once
2. **Start with models**: Models are the foundation, migrate them first
3. **Test frequently**: Test each model and controller as you migrate it
4. **Keep views simple**: Minimize changes to views during migration
5. **Use relationships**: Leverage Eloquent relationships instead of manual joins
6. **Type hints**: Add type hints to all methods (parameters and return types)
7. **Follow examples**: Use the existing migrated models as templates

## Getting Help

- **Examples**: See `Modules/Invoices/Entities/Invoice.php` for a complete model example
- **Documentation**: Read `.github/copilot-instructions.md` for detailed patterns
- **Eloquent Docs**: https://laravel.com/docs/10.x/eloquent
- **Questions**: Ask in the development chat or open an issue

## Progress Tracking

Track your migration progress in the main issue/PR. Mark completed models and controllers.

### Models to Migrate (~45 remaining)

**Invoices Module:**
- [ ] InvoiceAmount
- [ ] InvoiceItem (example provided above)
- [ ] InvoiceTaxRate
- [ ] InvoiceGroup
- [ ] InvoiceRecurring
- [ ] InvoiceSumex
- [ ] Template

**Payments Module:**
- [ ] PaymentMethod

**Products Module:**
- [ ] Family
- [ ] Unit
- [ ] TaxRate

**Quotes Module:**
- [ ] Quote
- [ ] QuoteItem
- [ ] QuoteTaxRate
- [ ] QuoteAmount

**CRM Module:**
- [ ] Project
- [ ] Task
- [ ] UserClient

**Users Module:**
- [ ] Session

**Custom Module:**
- [ ] CustomField
- [ ] CustomValue

**Core Module:**
- [ ] Setting
- [ ] EmailTemplate
- [ ] Version
- [ ] Various configuration models

## Example PR Description

When submitting a migration PR:

```
## Migrated Invoice Items Module

### Changes
- Created `Modules/Invoices/Entities/InvoiceItem.php` 
- Migrated from `application/modules/invoices/models/Mdl_items.php`
- Added relationships: belongsTo Invoice, belongsTo Product
- Added scopes: byInvoice, recurring
- All queries tested and working

### Testing
- [x] Create item
- [x] Read item
- [x] Update item  
- [x] Delete item
- [x] List items by invoice
- [x] Load with relationships

### Files Changed
- New: Modules/Invoices/Entities/InvoiceItem.php
```

Happy migrating! 🚀
