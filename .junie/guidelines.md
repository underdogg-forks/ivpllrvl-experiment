# InvoicePlane Modernization Guidelines

## Project Mission
Transform InvoicePlane from an ancient CodeIgniter 3 codebase into a modern, maintainable PHP application following modern PHP standards and best practices.

## Architecture Principles

### 1. Modern PHP Standards
- **PSR-4 Autoloading**: Namespaced classes with proper directory structure
- **PSR-12 Code Style**: Consistent formatting across codebase
- **Type Declarations**: Use type hints and return types
- **No Global Defines**: Use path helper functions instead
- **Proper Error Handling**: try/catch/finally blocks with centralized exception handling

### 2. Separation of Concerns
- **Source vs. Built**: Source files in `resources/`, built files in `public/`
- **Public directory**: All web-accessible files in `public/` for security
- **Storage organization**: Uploads in `storage/uploads/`, logs in `application/logs/`
- **Configuration**: Centralized in `config/` with filesystem.php for storage

### 3. Path Management
- **No Defines**: Eliminated all path defines from `public/index.php`
- **Path Helpers**: Use functions like `uploads_path()`, `logs_path()`, etc.
- **Clean Code**: `uploads_temp_path('file.pdf')` instead of `UPLOADS_TEMP_FOLDER . 'file.pdf'`

## Naming Conventions

### Controllers (PSR-4) ✅
```php
// File: application/Modules/Invoices/Controllers/InvoicesController.php
namespace App\Modules\Invoices\Controllers;

use Admin_Controller;

class InvoicesController extends Admin_Controller
{
    public function index(): void
    {
        // ...
    }
}
```

### Models (PSR-4) ✅
```php
// File: application/Modules/Invoices/Models/Invoice.php
namespace App\Modules\Invoices\Models;

use Response_Model;

class Invoice extends Response_Model
{
    protected string $table = 'ip_invoices';
}
```

### Libraries (PSR-4) ✅
```php
// File: application/Libraries/Cryptor.php
namespace App\Libraries;

class Cryptor
{
    // Modern library implementation
}
```

## Directory Structure

### Root Level
```
./
├── public/                 # Web root
│   ├── index.php          # Entry point (loads path helpers early)
│   └── assets/            # Compiled assets (CSS, JS, fonts)
├── storage/               # Storage directory
│   ├── uploads/           # All uploads (moved from root)
│   │   ├── archive/       # Archived PDFs
│   │   ├── customer_files/# Customer file uploads
│   │   ├── temp/          # Temporary files
│   │   └── import/        # Import files
│   ├── framework/         # Framework cache
│   └── logs/              # Storage logs
├── resources/             # Source files
│   └── assets/            # SASS/SCSS source files
├── application/           # CodeIgniter application
│   ├── Modules/           # HMVC modules (capital M)
│   │   └── {Module}/
│   │       ├── Controllers/  # PSR-4 controllers
│   │       ├── Models/       # PSR-4 models
│   │       └── views/        # Views
│   ├── Libraries/         # PSR-4 libraries (capital L)
│   ├── Core/              # Extended CI core classes
│   ├── helpers/           # Helper functions
│   ├── hooks/             # Application hooks
│   └── logs/              # Application logs
├── config/                # Configuration
│   ├── filesystem.php     # Storage disk configuration
│   └── config.php         # Main config
├── vendor/                # Composer dependencies
├── composer.json          # PHP dependencies
├── package.json           # Node dependencies
└── Gruntfile.js           # Asset build configuration
```

## Path Helpers - Modern Approach

### Core Path Helpers
```php
// Load helpers (done automatically in public/index.php)
require_once APPPATH . 'helpers/path_helper.php';

// Use path helpers throughout code
$log = logs_path('requests-' . date('Y-m-d') . '.php');
$config = config_path('database.php');
$upload = uploads_path('customer_files/file.pdf');
```

### Upload-Specific Helpers
```php
// Main uploads directory
uploads_path()                     // storage/uploads/

// Subdirectories
uploads_temp_path('file.pdf')      // storage/uploads/temp/file.pdf
uploads_archive_path('inv.pdf')    // storage/uploads/archive/inv.pdf
uploads_customer_files_path()      // storage/uploads/customer_files/

// Clean path joining
join_paths(base_path(), 'storage', 'cache', 'file.txt')
```

### NO MORE DEFINES! ❌
```php
// OLD - Don't do this anymore
define('UPLOADS_FOLDER', ...);
$file = UPLOADS_FOLDER . 'temp/file.pdf';

// NEW - Use helpers directly
$file = uploads_temp_path('file.pdf');
```

## Exception Handling

### Global Exception Handler
The application uses a centralized exception handler in `application/hooks/ExceptionHandler.php`:

```php
// Configured in config/hooks.php
$hook['pre_system'] = [
    'class'    => 'ExceptionHandler',
    'function' => 'init',
    'filename' => 'ExceptionHandler.php',
    'filepath' => 'hooks',
];
```

### Features
- **Whoops Integration**: Beautiful error pages in development
- **Automatic Logging**: All exceptions logged to `application/logs/exceptions-{date}.php`
- **Production Safety**: User-friendly error pages in production
- **Fatal Error Handling**: Catches even fatal errors via shutdown handler

### Usage in Code
```php
try {
    // Your code
    $result = someRiskyOperation();
} catch (NotFoundException $e) {
    // Handle specific exception
    log_message('error', $e->getMessage());
    show_404();
} catch (Exception $e) {
    // Let global handler catch it
    throw $e;
} finally {
    // Cleanup always runs
    cleanup_resources();
}
```

## Public/index.php Structure

### Modern Bootstrap
```php
// 1. Load environment (.env via ipconfig.php)
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__), 'ipconfig.php');
$dotenv->load();

// 2. Define core constants (BASEPATH, APPPATH, FCPATH)
define('BASEPATH', $system_path);
define('APPPATH', $application_folder . DIRECTORY_SEPARATOR);
define('FCPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// 3. Load path helpers EARLY
require_once APPPATH . 'helpers/path_helper.php';

// 4. NO path defines - use helpers instead!
// ❌ define('UPLOADS_FOLDER', ...);
// ✅ Just use uploads_path() in your code

// 5. Bootstrap CodeIgniter
require_once BASEPATH . 'core/CodeIgniter.php';
```

## Modernization Status

### ✅ Completed
- [x] PSR-4 autoloading for Modules, Libraries, Core
- [x] MX supports PSR-4 controllers and models
- [x] Dynamic inflector for model pluralization
- [x] Path helpers replace all defines
- [x] Uploads moved to storage/uploads/
- [x] Filesystem configuration (config/filesystem.php)
- [x] Libraries consolidated (only capital-L Libraries/)
- [x] Exception handler with Whoops integration
- [x] Modern error logging

### 🎯 Best Practices
1. **Always use path helpers** - Never concatenate paths with defines
2. **Use PSR-4 naming** - For all new code
3. **Type hint everything** - Use strict types where possible
4. **Exception handling** - Use try/catch/finally blocks
5. **Log errors properly** - Let ExceptionHandler catch and log
6. **Test your changes** - Lint with `php -l`, run application

## Quick Reference

### Path Helpers
- `app_path()` - application/
- `base_path()` - project root
- `public_path()` - public/
- `storage_path()` - storage/
- `config_path()` - config/
- `uploads_path()` - storage/uploads/
- `logs_path()` - application/logs/
- `view_path()` - application/views/
- `asset_path()` - public/assets/

### Model Pluralization
```php
// Automatic via inflector helper
$this->load->model('clients/client');
$this->client->get_all();     // Singular
$this->clients->get_all();    // Plural (auto-created)
```

### Storage Locations
- Uploads: `storage/uploads/`
- Archive: `storage/uploads/archive/`
- Customer files: `storage/uploads/customer_files/`
- Temp files: `storage/uploads/temp/`
- Logs: `application/logs/`

For complete details, see:
- **.github/copilot-instructions.md**: Comprehensive coding guidelines
- **PATH_MODERNIZATION.md**: Path helper migration guide
- **STRUCTURE_MODERNIZATION.md**: Technical modernization details
