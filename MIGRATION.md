# CodeIgniter to Laravel/Illuminate Migration

This document outlines the migration process from CodeIgniter 3 to Laravel/Illuminate components with PSR-4 autoloading.

## Overview

InvoicePlane is being modernized from a CodeIgniter 3 HMVC application to a modern architecture using standalone Illuminate components (Laravel packages without the framework) and PSR-4 autoloading via nwidart/laravel-modules.

## Architecture

### Before (CodeIgniter 3)
- **Framework**: CodeIgniter 3.1.13
- **Structure**: HMVC using Modular Extensions (MX)
- **Location**: `application/modules/`
- **Database**: CodeIgniter Query Builder (`$this->db`)
- **Views**: CodeIgniter View Loader (`$this->load->view()`)
- **Autoloading**: CodeIgniter's custom autoloader

### After (Illuminate Components)
- **Framework**: Standalone Illuminate components v10
- **Structure**: PSR-4 modular architecture using nwidart/laravel-modules
- **Location**: `Modules/`
- **Database**: Eloquent ORM
- **Views**: Illuminate View with plain PHP templates
- **Autoloading**: Composer PSR-4 autoloading

## Module Structure

The application is organized into 8 modules:

1. **Core** - Settings, Dashboard, Layout, Setup
2. **Invoices** - Invoice management and invoice groups
3. **Payments** - Payment and payment method management
4. **Products** - Product, families, units, tax rates
5. **Quotes** - Quote management
6. **Crm** - Clients, projects, tasks (Customer Relationship Management)
7. **Users** - User and session management
8. **Custom** - Custom fields and values

### Module Directory Structure

```
Modules/
└── ModuleName/
    ├── Config/
    │   └── config.php              # Module configuration
    ├── Http/
    │   └── Controllers/            # PSR-4 controllers
    ├── Entities/                   # Eloquent models
    ├── Resources/
    │   └── views/                  # Plain PHP view templates
    ├── Providers/
    │   ├── ModuleServiceProvider.php
    │   └── RouteServiceProvider.php
    ├── Routes/                     # Route definitions
    ├── composer.json               # Module dependencies
    └── module.json                 # Module metadata
```

## Migration Steps

### Step 1: Setup Dependencies ✅
- [x] Add Illuminate packages (container, database, view, support, events, filesystem)
- [x] Add nwidart/laravel-modules
- [x] Configure PSR-4 autoloading in composer.json
- [x] Create bootstrap files

### Step 2: Create Module Structure ✅
- [x] Create 8 modules (Core, Invoices, Payments, Products, Quotes, Crm, Users, Custom)
- [x] Generate module.json for each module
- [x] Generate composer.json for each module
- [x] Create service providers for each module

### Step 3: Bootstrap Illuminate ✅
- [x] Create bootstrap/app.php (initializes container, database, views)
- [x] Create bootstrap/helpers.php (helper functions)
- [x] Configure Illuminate Database with Eloquent
- [x] Configure Illuminate View for plain PHP templates

### Step 4: Create Base Classes ✅
- [x] Create App\Models\BaseModel (Eloquent base model)
- [x] Create example models (Invoice, Client, Payment, Product, User)
- [x] Create example controller (InvoiceController)

### Step 5: Migrate Models (In Progress)
Example models created:
- [x] Invoice (Modules\Invoices\Entities\Invoice)
- [x] Client (Modules\Crm\Entities\Client)
- [x] Payment (Modules\Payments\Entities\Payment)
- [x] Product (Modules\Products\Entities\Product)
- [x] User (Modules\Users\Entities\User)

Remaining models to migrate: ~50+ models across all modules

### Step 6: Migrate Controllers (Planned)
- [ ] Create base controller classes
- [ ] Migrate existing controllers to PSR-4 structure
- [ ] Update to use dependency injection instead of $this->load

### Step 7: Update Bootstrap (Planned)
- [ ] Modify index.php to bootstrap Illuminate
- [ ] Keep backward compatibility during transition
- [ ] Remove CodeIgniter bootstrap once migration complete

### Step 8: Remove CodeIgniter (Planned)
- [ ] Remove codeigniter/framework from composer.json
- [ ] Remove application/third_party/MX
- [ ] Clean up old CodeIgniter core files

## Key Differences

### Database Queries

**Before (CodeIgniter):**
```php
$this->db->select('*');
$this->db->where('client_id', $id);
$this->db->join('ip_clients', 'ip_clients.client_id = ip_invoices.client_id');
$query = $this->db->get('ip_invoices');
$result = $query->result();
```

**After (Eloquent):**
```php
use Modules\Invoices\Entities\Invoice;

$invoices = Invoice::where('client_id', $id)
    ->with('client')
    ->get();
```

### Models

**Before (CodeIgniter):**
```php
class Mdl_Invoices extends Response_Model
{
    public $table = 'ip_invoices';
    public $primary_key = 'ip_invoices.invoice_id';
    
    public function get_by_id($id)
    {
        $this->db->where('invoice_id', $id);
        return $this->db->get($this->table)->row();
    }
}
```

**After (Eloquent):**
```php
namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

class Invoice extends BaseModel
{
    protected $table = 'ip_invoices';
    protected $primaryKey = 'invoice_id';
    public $timestamps = false;
    
    protected $fillable = ['client_id', 'invoice_number', ...];
    
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}

// Usage:
$invoice = Invoice::find($id);
$invoice = Invoice::with('client')->find($id);
```

### Controllers

**Before (CodeIgniter):**
```php
class Invoices extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('mdl_invoices');
    }
    
    public function index()
    {
        $data['invoices'] = $this->mdl_invoices->get();
        $this->load->view('invoice_index', $data);
    }
}
```

**After (PSR-4):**
```php
namespace Modules\Invoices\Http\Controllers;

use Modules\Invoices\Entities\Invoice;

class InvoiceController
{
    public function index()
    {
        $invoices = Invoice::all();
        return view('invoices::index', compact('invoices'));
    }
}
```

### Views

**Before (CodeIgniter):**
```php
$this->load->view('invoice_index', $data);
```

**After (Illuminate):**
```php
return view('invoices::index', compact('data'));
// or
return view('invoices::index', ['data' => $value]);
```

View files remain as plain PHP (not Blade):
```php
<!-- Modules/Invoices/Resources/views/index.php -->
<h1>Invoices</h1>
<?php foreach ($invoices as $invoice): ?>
    <p><?php echo $invoice->invoice_number; ?></p>
<?php endforeach; ?>
```

## Helper Functions

New global helper functions available:

- `app($abstract)` - Get service from container
- `view($view, $data)` - Render a view
- `config_path($path)` - Get config path
- `base_path($path)` - Get base path
- `module_path($module, $path)` - Get module path
- `env($key, $default)` - Get environment variable

## Code Standards

All new code follows:
- **PSR-4** - Autoloading
- **PSR-12** - Coding style
- Modern PHP 8.1+ features (type hints, return types, etc.)

## Running Code Quality Tools

```bash
# Format code
composer pint

# Modernize code
composer rector

# Fix code style
composer phpcs

# Run all checks
composer check
```

## Examples

See the following example files for migration patterns:

1. **Models**:
   - `Modules/Invoices/Entities/Invoice.php`
   - `Modules/Crm/Entities/Client.php`
   - `Modules/Payments/Entities/Payment.php`
   - `Modules/Products/Entities/Product.php`
   - `Modules/Users/Entities/User.php`

2. **Controllers**:
   - `Modules/Invoices/Http/Controllers/InvoiceController.php`

3. **Base Classes**:
   - `app/Models/BaseModel.php`

## Documentation

For detailed architecture and patterns, see:
- `.github/copilot-instructions.md` - Comprehensive guide for GitHub Copilot

## Current Status

The foundation is in place:
- ✅ Illuminate components installed and configured
- ✅ Module structure created (8 modules)
- ✅ PSR-4 autoloading configured
- ✅ Example models and controllers created
- ✅ Documentation written

Next steps:
- Migrate remaining models from CodeIgniter to Eloquent
- Migrate controllers to new structure
- Update views to use new view helper
- Update index.php to use new bootstrap
- Remove CodeIgniter dependencies

## Notes

- Database tables remain unchanged (no schema changes required)
- Plain PHP views are maintained (not migrating to Blade)
- CodeIgniter helpers in `application/helpers/` can still be used during transition
- Environment configuration via `ipconfig.php` remains the same
