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

### ⚠️ CRITICAL: One-to-One Migration Required

**This is a COMPLETE migration, NOT a simplification or rewrite!**

When migrating code from `application/modules/` to `Modules/`:

1. **EVERY method must be migrated** - Do not simplify or omit any business logic
2. **EVERY function must have the same functionality** - Convert syntax, not behavior
3. **Method counts must match** - If legacy has 30 methods, new must have 30 methods
4. **Verify completeness** - Always compare method lists before and after migration
5. **No shortcuts** - Each method needs individual attention and proper conversion

### PSR-4 Naming Requirements (STRICT)

**Class names MUST NOT contain underscores!**

❌ **WRONG:**
```php
class Quote_item extends BaseModel           // Underscore in name
class Tax_ratesController                     // Underscore in name
class Invoice_groupsController                // Underscore in name
```

✅ **CORRECT:**
```php
class QuoteItem extends BaseModel            // PascalCase, no underscores
class TaxRatesController                      // PascalCase, no underscores  
class InvoiceGroupsController                 // PascalCase, no underscores
```

**File names MUST match class names:**
- `QuoteItem.php` not `Quote_item.php`
- `TaxRatesController.php` not `Tax_ratesController.php`

**CRITICAL PSR-4 RULES:**
1. Class names: PascalCase, NO underscores (e.g., `QuoteItem`, `InvoiceAmount`)
2. Namespaces: PascalCase, match directory structure
3. File names: MUST exactly match class name + `.php`
4. One class per file
5. Namespace must match: `Modules\{Module}\{SubDir}\{ClassName}`

### When Writing New Code

1. **Always use PSR-4 namespaces**: Place new code in `Modules/` with proper namespacing
2. **Use Eloquent for database**: Replace `$this->db` with Eloquent methods
3. **Use dependency injection**: Don't use `$this->load->` anymore
4. **Follow PSR-12 coding standards**: Use modern PHP features
5. **Use type hints**: Add parameter and return types to all methods
6. **Strict PSR-4 naming**: NO underscores in class names, use PascalCase

### When Migrating Existing Code (REQUIRED PROCESS)

**Step 1: Analysis**
1. Count all methods in legacy model/controller
2. List all method names
3. Identify dependencies (other models, helpers, libraries)
4. Note special CodeIgniter features used

**Step 2: Create Target Structure**
1. **Identify the module**: Determine which module the code belongs to (Core, Invoices, Payments, etc.)
2. **Choose PSR-4 compliant name**: Convert `Mdl_quote_items` → `QuoteItem` (NO underscores!)
3. **Create namespace**: `Modules\{Module}\Entities\{ClassName}`

**Step 3: Migrate Methods (ONE-TO-ONE)**
1. **Create Eloquent model**: Convert CodeIgniter model to Eloquent in `Modules/{Module}/Entities/`
2. **Migrate EVERY method**: Convert each method individually, maintaining all logic
3. **Convert syntax, not logic**:
   - `$this->db->where()` → Eloquent query builder
   - `$this->load->model()` → use statements and dependency injection
   - `$this->db->insert()` → `Model::create()`
4. **Preserve business logic**: Complex calculations, validations, etc. must remain identical

**Step 4: Migrate Controllers**
1. **Update controllers**: Convert to PSR-4 controllers in `Modules/{Module}/Http/Controllers/`
2. **PSR-4 naming**: `Quotes` → `QuotesController` (append `Controller` suffix)
3. **No underscores**: `Invoice_groups` → `InvoiceGroupsController`
4. **Migrate all methods**: Every action must be migrated

**Step 5: Migrate Views**
1. **Keep views as PHP**: Move views to `Modules/{Module}/Resources/views/` (keep as plain PHP)
2. **Update view calls**: `$this->load->view('view')` → `view('module::view')`
3. **Preserve all views**: Don't skip any view files

**Step 6: Verification**
1. **Compare method counts**: Legacy vs new must match
2. **Test critical paths**: Ensure calculations work (especially invoice/quote totals)
3. **Check PSR-4 compliance**: Run linters to verify naming
4. **Only after verification**: Remove legacy files

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

### Naming Conventions (PSR-4/PSR-12 STRICT)

- **Classes**: PascalCase, NO UNDERSCORES (e.g., `InvoiceController`, `Invoice`, `QuoteItem` not `Quote_item`)
- **Methods**: camelCase (e.g., `getInvoices()`, `createInvoice()`)
- **Properties**: camelCase (e.g., `$invoiceNumber`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `STATUS_PAID`)
- **Namespaces**: PascalCase, match directory structure exactly

**Common Naming Conversions:**
- `Mdl_quote_items` → `QuoteItem` (remove Mdl_ prefix, convert to PascalCase)
- `Mdl_invoice_amounts` → `InvoiceAmount`
- `Invoice_groups` → `InvoiceGroupsController` (add Controller suffix, PascalCase)
- `Tax_rates` → `TaxRatesController`
- `Quote_tax_rate` → `QuoteTaxRate`

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

### Module Mapping (Legacy → New)

| Legacy Module | New Module | Status | Notes |
|--------------|------------|--------|-------|
| `clients` | `Crm` | ❌ Not migrated | Client, ClientNote models missing |
| `projects` | `Crm` | ❌ Not migrated | Project model missing |
| `tasks` | `Crm` | ❌ Not migrated | Task model missing |
| `custom_fields` | `Custom` | ❌ Not migrated | 6 models + controller missing |
| `custom_values` | `Custom` | ❌ Not migrated | Model + controller missing |
| `invoices` | `Invoices` | ⚠️ Incomplete | 32→15 methods, missing business logic |
| `invoice_groups` | `Invoices` | ❌ Not migrated | InvoiceGroup model incomplete |
| `quotes` | `Quotes` | ⚠️ Incomplete | 30→10 methods, missing business logic |
| `payments` | `Payments` | ❌ Not migrated | Payment, PaymentLog models missing |
| `payment_methods` | `Payments` | ❌ Not migrated | PaymentMethod model missing |
| `products` | `Products` | ❌ Not migrated | Product model missing |
| `families` | `Products` | ❌ Not migrated | Family model missing |
| `tax_rates` | `Products` | ❌ Not migrated | TaxRate model missing |
| `units` | `Products` | ❌ Not migrated | Unit model missing |
| `users` | `Users` | ❌ Not migrated | User model missing |
| `sessions` | `Users` | ❌ Not migrated | Session model missing |
| `user_clients` | `Users` | ❌ Not migrated | UserClient model + controller missing |
| `dashboard` | `Core` | ⚠️ Partial | Controller only |
| `settings` | `Core` | ❌ Not migrated | Settings, Versions models missing |
| `setup` | `Core` | ⚠️ Incomplete | 12→0 methods missing |
| `layout` | `Core` | ⚠️ Partial | Controller only |
| `email_templates` | TBD | ❌ Unmapped | Needs module assignment |
| `upload` | TBD | ❌ Unmapped | Needs module assignment |
| `mailer` | TBD | ❌ Unmapped | Needs module assignment |
| `guest` | TBD | ❌ Unmapped | Needs module assignment (7 controllers!) |
| `reports` | TBD | ❌ Unmapped | Needs module assignment |
| `import` | TBD | ❌ Unmapped | Needs module assignment |
| `filter` | TBD | ❌ Unmapped | Needs module assignment |
| `welcome` | TBD | ❌ Unmapped | Needs module assignment |

### Critical Missing Functionality

**Invoices Module:**
- Missing methods in `Invoice`: `create()`, `copy_invoice()`, `copy_credit_invoice()`, `db_array()`, `get_payments()`, `get_date_due()`, `get_invoice_number()`, `get_url_key()`, `mark_viewed()`, `mark_sent()`, `generate_invoice_number_if_applicable()`, and 10+ more
- Missing `InvoiceAmount` calculation methods (9 methods)
- Missing `Item` business logic (7 methods)
- Missing `InvoiceTaxRate` calculations (4 methods)

**Quotes Module:**
- Missing methods in `Quote`: `create()`, `copy_quote()`, `db_array()`, `get_date_due()`, `get_quote_number()`, `get_url_key()`, `approve_quote_by_key()`, `reject_quote_by_key()`, `mark_viewed()`, `mark_sent()`, `generate_quote_number_if_applicable()`, and 10+ more  
- Missing `QuoteAmount` calculation methods (7 methods)
- Missing `QuoteItem` business logic (7 methods)
- Missing `QuoteTaxRate` calculations (4 methods)

### PSR-4 Naming Violations (MUST FIX)

Files with underscores in class names (non-compliant):
- `Modules/Quotes/Entities/Quote_amount.php` → Should be `QuoteAmount.php`
- `Modules/Quotes/Entities/Quote_item.php` → Should be `QuoteItem.php`
- `Modules/Quotes/Entities/Quote_item_amount.php` → Should be `QuoteItemAmount.php`
- `Modules/Quotes/Entities/Quote_tax_rate.php` → Should be `QuoteTaxRate.php`
- `Modules/Crm/Http/Controllers/User_clientsController.php` → Should be `UserClientsController.php`
- `Modules/Crm/Http/Controllers/Payment_informationController.php` → Should be `PaymentInformationController.php`
- `Modules/Crm/Entities/User_client.php` → Should be `UserClient.php`
- `Modules/Crm/Entities/Client_note.php` → Should be `ClientNote.php`
- `Modules/Products/Http/Controllers/Tax_ratesController.php` → Should be `TaxRatesController.php`
- `Modules/Products/Entities/Tax_rate.php` → Should be `TaxRate.php`
- `Modules/Core/Http/Controllers/Custom_fieldsController.php` → Should be `CustomFieldsController.php`
- `Modules/Core/Http/Controllers/Custom_valuesController.php` → Should be `CustomValuesController.php`
- `Modules/Core/Http/Controllers/Email_templatesController.php` → Should be `EmailTemplatesController.php`
- And 7+ more entity classes with underscores

### Completed
- ✅ Illuminate components installed
- ✅ Module structure created (8 modules)
- ✅ PSR-4 autoloading configured
- ✅ Base model created
- ✅ Service providers generated
- ✅ Bootstrap files created

### In Progress
- 🔄 Migrating models from CodeIgniter to Eloquent (INCOMPLETE - missing ~40+ models)
- 🔄 Converting controllers to PSR-4 (INCOMPLETE - missing ~15+ controllers)
- 🔄 Fixing PSR-4 naming violations (~20+ files)

### Pending
- ⏳ Complete one-to-one method migration for all models
- ⏳ Migrate all 8 unmapped modules
- ⏳ Fix all PSR-4 naming violations
- ⏳ Migrate all views
- ⏳ Remove legacy files after verification
- ⏳ Remove CodeIgniter framework dependency
- ⏳ Remove MX (Modular Extensions)
- ⏳ Update index.php bootstrap

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
