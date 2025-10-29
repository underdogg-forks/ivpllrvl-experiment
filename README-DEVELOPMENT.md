# InvoicePlane - Modern Architecture

## Quick Start for Developers

This project is in the process of migrating from CodeIgniter 3 to a modern architecture using Laravel/Illuminate components with PSR-4 autoloading.

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/underdogg-forks/ivpllrvl-experiment.git
   cd ivpllrvl-experiment
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp ipconfig.php.example ipconfig.php
   # Edit ipconfig.php with your database credentials
   ```

4. **Set permissions**
   ```bash
   chmod -R 777 storage/
   chmod -R 777 uploads/
   chmod -R 777 application/logs/
   ```

### Architecture Overview

#### Hybrid Structure (Transitional State)

The application currently runs in a hybrid mode:

**Legacy (CodeIgniter 3):**
- Location: `application/modules/`
- Still functional and serving production traffic
- Uses CodeIgniter Query Builder for database
- Uses CodeIgniter view loader

**Modern (Illuminate/PSR-4):**
- Location: `Modules/`
- New code should be written here
- Uses Eloquent ORM for database
- Uses Illuminate View engine (plain PHP templates)
- Full PSR-4 autoloading

### Module Structure

```
Modules/
├── Core/           # Settings, Dashboard, Layout, Setup
├── Invoices/       # Invoice management
├── Payments/       # Payments and payment methods
├── Products/       # Products, families, units, tax rates
├── Quotes/         # Quote management
├── Crm/            # Clients, projects, tasks
├── Users/          # Users and sessions
└── Custom/         # Custom fields and values
```

### Development Guidelines

#### Creating a New Model

Place models in `Modules/{ModuleName}/Entities/`:

```php
<?php

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
```

#### Creating a New Controller

Place controllers in `Modules/{ModuleName}/Http/Controllers/`:

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
}
```

#### Database Queries with Eloquent

```php
// Simple query
$invoices = Invoice::all();

// With conditions
$invoices = Invoice::where('client_id', $id)->get();

// With relationships
$invoice = Invoice::with(['client', 'items'])->find($id);

// Complex query
$invoices = Invoice::where('invoice_status_id', 1)
    ->orWhere(function($query) {
        $query->where('invoice_status_id', 2)
              ->where('client_id', 123);
    })
    ->orderBy('invoice_date_created', 'desc')
    ->paginate(15);
```

### Code Quality Tools

```bash
# Format code (PSR-12)
composer pint

# Modernize code  
composer rector

# Fix code style
composer phpcs

# Run all checks
composer check
```

### Documentation

- **[MIGRATION.md](MIGRATION.md)** - Complete migration guide with examples
- **[.github/copilot-instructions.md](.github/copilot-instructions.md)** - Architecture guide for AI assistants
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Contribution guidelines

### File Structure

```
.
├── app/                    # Application classes
│   └── Models/
│       └── BaseModel.php   # Base Eloquent model
├── application/            # Legacy CodeIgniter code
│   ├── modules/            # Old HMVC modules
│   ├── controllers/
│   ├── models/
│   └── views/
├── assets/                 # Public assets (CSS, JS, images)
├── bootstrap/              # Application bootstrap
│   ├── app.php            # Illuminate container setup
│   └── helpers.php        # Global helper functions
├── config/                 # Configuration files
│   └── modules.php        # Module configuration
├── Modules/               # New PSR-4 modules
│   ├── Core/
│   ├── Invoices/
│   ├── Payments/
│   ├── Products/
│   ├── Quotes/
│   ├── Crm/
│   ├── Users/
│   └── Custom/
├── resources/             # Resources
├── storage/               # Storage for logs, cache, etc.
│   ├── framework/views/   # Compiled views
│   └── modules_statuses.json  # Active modules
├── uploads/               # User uploads
├── vendor/                # Composer dependencies
├── index.php              # Legacy entry point (CodeIgniter)
├── index-new.php          # New entry point (Illuminate)
├── composer.json          # PHP dependencies
└── MIGRATION.md          # Migration documentation
```

### Environment Variables

Configuration is done via `ipconfig.php` (uses phpdotenv):

```ini
# Database
DB_HOSTNAME=localhost
DB_USERNAME=root
DB_PASSWORD=
DB_DATABASE=invoiceplane
DB_PORT=3306

# Application
ENABLE_DEBUG=false
DISABLE_SETUP=false

# Toggle new bootstrap (once migration complete)
USE_NEW_BOOTSTRAP=false
```

### Current Status

**✅ Complete:**
- Illuminate components installed and configured
- 8 modules created with proper structure
- PSR-4 autoloading configured
- Example models and controllers
- Comprehensive documentation

**🔄 In Progress:**
- Migrating models to Eloquent
- Migrating controllers to PSR-4

**⏳ Pending:**
- Complete controller migration
- Switch to new bootstrap (index-new.php)
- Remove CodeIgniter dependency

### Contributing

When adding new features:

1. **Use the new module structure** (`Modules/`)
2. **Follow PSR-4 and PSR-12** coding standards
3. **Use Eloquent** for database operations
4. **Add type hints** to all methods
5. **Document your code** with PHPDoc blocks

### Testing

Currently, there is no automated test suite. Testing is done manually by:

1. Running the application
2. Testing each feature
3. Verifying database operations

### Common Tasks

**Add a new module:**
```bash
# Create module structure
mkdir -p Modules/NewModule/{Config,Http/Controllers,Entities,Resources/views,Providers}

# Copy and adapt files from existing modules
# Update storage/modules_statuses.json
```

**Query the database:**
```php
use Modules\Invoices\Entities\Invoice;

// In controller or anywhere with autoloading
$invoice = Invoice::find($id);
$invoices = Invoice::where('client_id', $clientId)->get();
```

**Render a view:**
```php
return view('modulename::viewname', ['data' => $value]);
```

### Support

- **Documentation:** See MIGRATION.md and .github/copilot-instructions.md
- **Issues:** [GitHub Issues](https://github.com/InvoicePlane/InvoicePlane/issues)
- **Community:** [InvoicePlane Forums](https://community.invoiceplane.com/)

### License

InvoicePlane is licensed under the MIT License. See [LICENSE.txt](LICENSE.txt).
