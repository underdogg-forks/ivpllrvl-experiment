# GitHub Copilot Instructions for InvoicePlane

## Project Overview

InvoicePlane is a self-hosted open source application for managing invoices, clients and payments. This project is in the process of being migrated from CodeIgniter 3 to a modern Laravel/Illuminate-based architecture with PSR-4 autoloading.

## Architecture

### Current State (Transitional)

The application is currently in a hybrid state:
- **Legacy Code**: CodeIgniter 3 with HMVC (Modular Extensions) in `application/modules/`
- **New Code**: Laravel/Illuminate components with PSR-4 modules in `Modules/`

### New Architecture Components

#### 1. Illuminate Components

The application now uses standalone Illuminate components (Laravel packages without the framework):

- **illuminate/database**: Eloquent ORM for database operations
- **illuminate/view**: View rendering engine (configured for plain PHP templates)
- **illuminate/container**: Dependency injection container
- **illuminate/support**: Helper functions and collections
- **illuminate/events**: Event dispatcher
- **illuminate/filesystem**: File operations

#### 2. Module Structure (nwidart/laravel-modules)

Modules are located in the `Modules/` directory with the following structure:

```
Modules/
├── Core/           # Settings, Dashboard, Layout, Setup
├── Invoices/       # Invoice management
├── Payments/       # Payment and payment method management
├── Products/       # Product, families, units, tax rates
├── Quotes/         # Quote management
├── Crm/            # Clients, projects, tasks
├── Users/          # User and session management
└── Custom/         # Custom fields and values
```

Each module follows this structure:
```
ModuleName/
├── Config/                 # Module configuration
├── Http/
│   └── Controllers/        # PSR-4 controllers
├── Entities/               # Eloquent models
├── Resources/
│   └── views/             # Plain PHP view templates
├── Providers/
│   ├── ModuleServiceProvider.php
│   └── RouteServiceProvider.php
├── Routes/                 # Route definitions
├── composer.json          # Module dependencies
└── module.json            # Module metadata
```

#### 3. PSR-4 Autoloading

All new code follows PSR-4 autoloading standards:

- **Modules**: `Modules\{ModuleName}\{Component}\{ClassName}`
  - Example: `Modules\Invoices\Entities\Invoice`
  - Example: `Modules\Invoices\Http\Controllers\InvoiceController`

- **App**: `App\{Component}\{ClassName}`
  - Example: `App\Models\BaseModel`

#### 4. Database (Eloquent ORM)

**OLD (CodeIgniter):**
```php
$this->db->select('*');
$this->db->where('client_id', $id);
$this->db->join('ip_clients', 'ip_clients.client_id = ip_invoices.client_id');
$query = $this->db->get('ip_invoices');
```

**NEW (Eloquent):**
```php
use Modules\Invoices\Entities\Invoice;

$invoices = Invoice::where('client_id', $id)
    ->with('client')
    ->get();
```

**Base Model:**
All Eloquent models should extend `App\Models\BaseModel`:

```php
namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

class Invoice extends BaseModel
{
    protected $table = 'ip_invoices';
    protected $primaryKey = 'invoice_id';
    public $timestamps = false;
    
    protected $fillable = [
        'client_id',
        'invoice_number',
        // ...
    ];
    
    public function client()
    {
        return $this->belongsTo('Modules\Crm\Entities\Client', 'client_id');
    }
}
```

#### 5. Views

Views use plain PHP (not Blade) and are rendered via Illuminate View:

**OLD (CodeIgniter):**
```php
$this->load->view('invoice_view', $data);
```

**NEW (Illuminate):**
```php
return view('invoices::invoice_view', $data);
```

View files remain as plain PHP:
```php
<!-- Modules/Invoices/Resources/views/invoice_view.php -->
<h1><?php echo $invoice->invoice_number; ?></h1>
```

#### 6. Controllers

**OLD (CodeIgniter):**
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
        $data = ['invoices' => $this->mdl_invoices->get()];
        $this->load->view('invoice_index', $data);
    }
}
```

**NEW (PSR-4 with Illuminate):**
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

## Migration Guidelines

### When Writing New Code

1. **Always use PSR-4 namespaces**: Place new code in `Modules/` with proper namespacing
2. **Use Eloquent for database**: Replace `$this->db` with Eloquent models
3. **Use dependency injection**: Don't use `$this->load->` anymore
4. **Follow PSR-12 coding standards**: Use modern PHP features
5. **Use type hints**: Add parameter and return types to all methods

### When Updating Existing Code

1. **Identify the module**: Determine which module the code belongs to (Core, Invoices, Payments, etc.)
2. **Create Eloquent model**: Convert CodeIgniter models to Eloquent models in `Modules/{Module}/Entities/`
3. **Update controllers**: Convert to PSR-4 controllers in `Modules/{Module}/Http/Controllers/`
4. **Keep views as PHP**: Move views to `Modules/{Module}/Resources/views/` (keep as plain PHP)
5. **Update database calls**: Replace `$this->db->` with Eloquent methods

### Bootstrap Files

- **bootstrap/app.php**: Initializes Illuminate container, database, and view engine
- **bootstrap/helpers.php**: Global helper functions (view(), app(), etc.)
- **config/modules.php**: Module configuration for nwidart/laravel-modules

### Helper Functions Available

- `app($abstract = null)`: Get service from container
- `view($view, $data)`: Render a view
- `config_path($path)`: Get config directory path
- `base_path($path)`: Get base directory path
- `module_path($module, $path)`: Get module directory path
- `env($key, $default)`: Get environment variable

## Code Style

### PSR-12 Standards

- Use 4 spaces for indentation
- Opening braces on same line for classes, methods
- Declare visibility for all properties and methods
- Use `declare(strict_types=1);` for new files
- One blank line after namespace declaration

### Naming Conventions

- **Classes**: PascalCase (e.g., `InvoiceController`, `Invoice`)
- **Methods**: camelCase (e.g., `getInvoices()`, `createInvoice()`)
- **Properties**: camelCase (e.g., `$invoiceNumber`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `STATUS_PAID`)

## Testing

Currently, there is no automated test suite. When adding tests:

1. Place in `tests/` directory
2. Use PHPUnit
3. Follow Laravel testing conventions

## Common Patterns

### Creating a New Model

```php
<?php

namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

class Invoice extends BaseModel
{
    protected $table = 'ip_invoices';
    protected $primaryKey = 'invoice_id';
    public $timestamps = false;
    
    protected $fillable = [
        'client_id',
        'invoice_number',
        'invoice_status_id',
    ];
    
    protected $casts = [
        'invoice_id' => 'integer',
        'client_id' => 'integer',
        'invoice_status_id' => 'integer',
    ];
    
    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
    
    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }
}
```

### Creating a New Controller

```php
<?php

namespace Modules\Invoices\Http\Controllers;

use Modules\Invoices\Entities\Invoice;

class InvoiceController
{
    public function index()
    {
        $invoices = Invoice::with('client')->get();
        return view('invoices::index', compact('invoices'));
    }
    
    public function show(int $id)
    {
        $invoice = Invoice::with(['client', 'items'])->findOrFail($id);
        return view('invoices::show', compact('invoice'));
    }
    
    public function store(array $data)
    {
        $invoice = Invoice::create($data);
        return $invoice;
    }
}
```

## Development Workflow

1. **Install dependencies**: `composer install`
2. **Run linters**: `composer check` (runs rector, phpcs, pint)
3. **Format code**: `composer pint`
4. **Modernize code**: `composer rector`

## Migration Progress

### Completed
- ✅ Illuminate components installed
- ✅ Module structure created (8 modules)
- ✅ PSR-4 autoloading configured
- ✅ Base model created
- ✅ Service providers generated
- ✅ Bootstrap files created

### In Progress
- 🔄 Migrating models from CodeIgniter to Eloquent
- 🔄 Converting controllers to PSR-4

### Pending
- ⏳ Remove CodeIgniter framework dependency
- ⏳ Remove MX (Modular Extensions)
- ⏳ Update index.php bootstrap
- ⏳ Complete migration of all modules

## Important Notes

- **Database tables** remain unchanged (prefix: `ip_`)
- **Plain PHP views** are still used (not Blade)
- **CodeIgniter helpers** in `application/helpers/` can still be used during transition
- **Environment configuration** via `ipconfig.php` (uses phpdotenv)

## Questions?

Refer to:
- [Eloquent Documentation](https://laravel.com/docs/10.x/eloquent)
- [Illuminate Container](https://laravel.com/docs/10.x/container)
- [nwidart/laravel-modules](https://nwidart.com/laravel-modules/)
