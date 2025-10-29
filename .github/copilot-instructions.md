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

## File Structure Overview

```
InvoicePlane/
├── .github/                    # GitHub configuration and workflows
│   ├── copilot-instructions.md # This file - Copilot AI instructions
│   └── workflows/              # CI/CD workflows
├── app/                        # New PSR-4 App namespace
│   └── Models/
│       └── BaseModel.php       # Base Eloquent model
├── application/                # Legacy CodeIgniter code
│   ├── modules/                # HMVC modules (being migrated)
│   ├── helpers/                # Global helper functions
│   └── config/                 # CodeIgniter configuration
├── assets/                     # Frontend assets (CSS, JS, images)
├── bootstrap/                  # New bootstrap files
│   ├── app.php                 # Illuminate container initialization
│   └── helpers.php             # Global helper functions
├── config/                     # New config files
│   └── modules.php             # Module configuration
├── Modules/                    # New PSR-4 module structure
│   ├── Core/                   # Settings, Dashboard, Layout
│   ├── Invoices/               # Invoice management
│   ├── Payments/               # Payment processing
│   ├── Products/               # Product catalog
│   ├── Quotes/                 # Quote management
│   ├── Crm/                    # Customer relations
│   ├── Users/                  # User management
│   └── Custom/                 # Custom fields
├── resources/                  # Additional resources
├── storage/                    # Storage for logs, cache, uploads
│   ├── framework/views/        # Compiled views
│   └── modules_statuses.json  # Module activation status
├── uploads/                    # User uploads
├── vendor/                     # Composer dependencies
├── composer.json               # PHP dependencies
├── package.json                # Node.js dependencies
├── index.php                   # Legacy entry point (CodeIgniter)
├── index-new.php               # New entry point (Illuminate)
└── ipconfig.php                # Environment configuration
```

## Testing

Currently, there is no automated test suite. When adding tests:

1. Place in `tests/` directory
2. Use PHPUnit
3. Follow Laravel testing conventions

## Security Best Practices

### When Writing Code

1. **Input Validation**: Always validate and sanitize user input
   ```php
   $validated = filter_var($input, FILTER_SANITIZE_STRING);
   ```

2. **SQL Injection Prevention**: Use Eloquent ORM or parameterized queries
   ```php
   // ✅ GOOD - Eloquent automatically escapes
   Invoice::where('client_id', $id)->get();
   
   // ❌ BAD - Raw queries without parameters
   DB::raw("SELECT * FROM invoices WHERE client_id = $id");
   ```

3. **XSS Prevention**: Escape output in views
   ```php
   <?php echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8'); ?>
   ```

4. **Authentication & Authorization**: Always check user permissions
   ```php
   if (!$this->session->userdata('user_id')) {
       redirect('sessions/login');
   }
   ```

5. **File Uploads**: Validate file types and sizes
   ```php
   $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
   $max_size = 2048; // 2MB
   ```

6. **Environment Variables**: Never hardcode sensitive data
   ```php
   // Use ipconfig.php or environment variables
   $api_key = env('API_KEY');
   ```

### Security Checklist

- [ ] Never commit `ipconfig.php` with real credentials
- [ ] Use prepared statements or Eloquent for database queries
- [ ] Validate all user input
- [ ] Escape all output
- [ ] Check user permissions before sensitive operations
- [ ] Use HTTPS in production
- [ ] Keep dependencies updated (run `composer update` regularly)

## Common Gotchas and Pitfalls

### 1. PSR-4 Naming Violations

**Problem**: Using underscores in class names breaks autoloading.

```php
// ❌ WRONG - Will not autoload
class Quote_item extends BaseModel { }

// ✅ CORRECT
class QuoteItem extends BaseModel { }
```

### 2. Database Table Prefixes

**Problem**: Forgetting the `ip_` prefix on table names.

```php
// ❌ WRONG
protected $table = 'invoices';

// ✅ CORRECT
protected $table = 'ip_invoices';
```

### 3. Timestamps

**Problem**: Eloquent expects `created_at` and `updated_at` columns by default.

```php
// Most InvoicePlane tables don't use timestamps
public $timestamps = false;
```

### 4. Primary Keys

**Problem**: Default primary key is `id`, but InvoicePlane uses `{table}_id`.

```php
// ✅ CORRECT
protected $primaryKey = 'invoice_id';
```

### 5. View Namespacing

**Problem**: Forgetting module namespace when calling views.

```php
// ❌ WRONG
return view('invoice_index', $data);

// ✅ CORRECT
return view('invoices::invoice_index', $data);
```

### 6. Relationship Foreign Keys

**Problem**: Eloquent guesses foreign keys if not specified.

```php
// ✅ EXPLICIT - Better for InvoicePlane's naming
public function client()
{
    return $this->belongsTo(Client::class, 'client_id', 'client_id');
}
```

### 7. Mass Assignment Protection

**Problem**: Forgetting to set `$fillable` allows mass assignment vulnerabilities.

```php
// ✅ ALWAYS define fillable fields
protected $fillable = [
    'client_id',
    'invoice_number',
    'invoice_date',
];
```

### 8. Type Casting

**Problem**: Database returns strings, but you need integers/decimals.

```php
// ✅ CAST data types
protected $casts = [
    'invoice_id' => 'integer',
    'invoice_total' => 'decimal:2',
    'invoice_paid' => 'boolean',
];
```

### 9. CodeIgniter vs Illuminate Helpers

**Problem**: Mixing CodeIgniter and Illuminate patterns.

```php
// ❌ WRONG in new code
$this->load->model('mdl_invoices');
$this->db->where('id', $id);

// ✅ CORRECT in new code
use Modules\Invoices\Entities\Invoice;
$invoice = Invoice::find($id);
```

### 10. Session Handling

**Problem**: Different session APIs between CodeIgniter and new code.

```php
// Legacy CodeIgniter
$this->session->userdata('user_id');

// New code - CodeIgniter helpers still work during transition
// Will eventually migrate to Illuminate session
```

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

### Local Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/InvoicePlane/InvoicePlane.git
   cd InvoicePlane
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**:
   ```bash
   cp ipconfig.php.example ipconfig.php
   # Edit ipconfig.php and set your database connection and base URL
   ```

4. **Set up database**:
   - Create a MySQL/MariaDB database
   - Navigate to `http://your-domain.com/index.php/setup` to run the installer

5. **Build frontend assets**:
   ```bash
   npm run grunt
   ```

### Development Commands

1. **Install dependencies**: `composer install`
2. **Run linters**: `composer check` (runs rector, phpcs, pint)
3. **Format code**: `composer pint`
4. **Modernize code**: `composer rector`
5. **Validate composer**: `composer validate --strict`
6. **Build assets**: `npm run grunt`

### Testing Locally

Currently, there is no automated test suite. Manual testing workflow:

1. **Start development server**:
   ```bash
   php -S localhost:8000
   ```
   Or use Docker:
   ```bash
   docker-compose up
   ```

2. **Access the application**: Navigate to `http://localhost:8000`

3. **Test changes**: Click through affected functionality to verify behavior

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

## Contribution Workflow

### Before Starting

1. **Check existing issues**: Look for related issues or create a new one
2. **Discuss major changes**: For significant changes, discuss in an issue first
3. **Review migration status**: Check the "Migration Progress" section above

### Pull Request Process

1. **Fork and branch**: Create a feature branch from `development`
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Follow conventions**:
   - Use PSR-4 naming (NO underscores in class names)
   - Follow PSR-12 code style
   - Add type hints to all methods
   - Document complex logic

3. **Test your changes**:
   ```bash
   # Format code
   composer pint
   
   # Run code quality checks
   composer check
   
   # Manually test functionality
   ```

4. **Commit with clear messages**:
   ```bash
   git commit -m "Add QuoteItem model with relationships"
   ```

5. **Push and create PR**:
   ```bash
   git push origin feature/your-feature-name
   ```

6. **PR Title Format**:
   - `feat: Add QuoteItem model` (new feature)
   - `fix: Correct invoice total calculation` (bug fix)
   - `refactor: Convert Quotes controller to PSR-4` (refactoring)
   - `docs: Update migration guide` (documentation)

7. **PR Description**: Include:
   - What was changed and why
   - Which legacy code was migrated (if applicable)
   - Testing steps
   - Related issue numbers

### Code Review Expectations

- PRs will be reviewed for:
  - PSR-4/PSR-12 compliance
  - Complete method migration (no missing business logic)
  - Proper type hints and documentation
  - Security best practices
  - Test coverage (when applicable)

### For Migration PRs

Use this checklist in your PR description:

```markdown
## Migration Checklist

- [ ] All methods from legacy code migrated (X of X methods)
- [ ] PSR-4 naming (no underscores in class names)
- [ ] Relationships defined for all foreign keys
- [ ] Type hints added to all methods
- [ ] Database queries converted to Eloquent
- [ ] Views updated to use new namespace
- [ ] Tested critical paths manually
- [ ] Code formatted with `composer pint`
- [ ] No phpcs errors (`composer check`)
```

## Performance Considerations

### Database Queries

1. **Use Eager Loading**: Prevent N+1 query problems
   ```php
   // ❌ BAD - N+1 queries
   $invoices = Invoice::all();
   foreach ($invoices as $invoice) {
       echo $invoice->client->name; // Queries client for each invoice
   }
   
   // ✅ GOOD - 2 queries total
   $invoices = Invoice::with('client')->get();
   foreach ($invoices as $invoice) {
       echo $invoice->client->name;
   }
   ```

2. **Select Only Needed Columns**:
   ```php
   // ✅ Better performance
   Invoice::select('invoice_id', 'invoice_number', 'invoice_total')->get();
   ```

3. **Use Query Scopes**: Reusable query logic
   ```php
   // In model
   public function scopeOverdue($query)
   {
       return $query->where('invoice_date_due', '<', date('Y-m-d'))
                    ->where('invoice_status_id', '!=', 4);
   }
   
   // Usage
   $overdue = Invoice::overdue()->get();
   ```

4. **Pagination**: For large datasets
   ```php
   $invoices = Invoice::paginate(50);
   ```

### Caching Considerations

InvoicePlane doesn't currently use caching extensively, but when adding caching:

- Cache expensive database queries
- Clear cache when data changes
- Use appropriate cache keys
- Consider using Illuminate Cache in the future

## Debugging Tips

### Enable Error Reporting

In `ipconfig.php`:
```php
define('ENVIRONMENT', 'development'); // Shows detailed errors
```

### Using Whoops

Whoops is installed for better error pages in development:
```php
// Automatically active when ENVIRONMENT is 'development'
```

### Database Query Debugging

```php
// Enable query log
DB::enableQueryLog();

// Your queries here
$invoices = Invoice::with('client')->get();

// See executed queries
dd(DB::getQueryLog());
```

### Common Debug Points

1. **Autoloading issues**: Check namespace matches directory structure
2. **View not found**: Verify module namespace (`invoices::view_name`)
3. **Relationship errors**: Check foreign key names
4. **Route not working**: Check if module is enabled in `storage/modules_statuses.json`

## Additional Resources

- [CONTRIBUTING.md](../CONTRIBUTING.md) - General contribution guidelines
- [MIGRATION-GUIDE.md](../MIGRATION-GUIDE.md) - Detailed migration instructions
- [README-DEVELOPMENT.md](../README-DEVELOPMENT.md) - Development setup
- [InvoicePlane Wiki](https://wiki.invoiceplane.com/) - User documentation
- [Community Forums](https://community.invoiceplane.com/) - Get help
- [Discord](https://discord.gg/PPzD2hTrXt) - Real-time chat
