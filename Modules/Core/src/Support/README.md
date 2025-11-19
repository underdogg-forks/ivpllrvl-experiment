# Helper Classes - Modules/Core/Support

This directory contains static helper classes that provide the core functionality for the InvoicePlane application.

## Architecture

All helper functions have been refactored from procedural functions to static methods in classes. This provides several benefits:

1. **Better Organization**: Related functions are grouped in classes
2. **Type Safety**: Strict types can be enforced
3. **Testability**: Static methods are easier to test
4. **IDE Support**: Better autocomplete and documentation
5. **Modern PHP**: Follows modern PHP best practices
6. **Backward Compatibility**: Old procedural functions still work via `bc_helper.php`
7. **No CodeIgniter Artifacts**: Uses LegacyBridge instead of direct `get_instance()` calls

## CodeIgniter Migration

**Important**: These helper classes no longer directly access CodeIgniter components using `$CI = &get_instance()`. 

Instead, they use the `LegacyBridge` service to access CodeIgniter functionality in a clean, centralized way:

```php
use Modules\Core\Services\LegacyBridge;

class DateHelper
{
    public static function dateFormatSetting()
    {
        $bridge = LegacyBridge::getInstance();
        $settings = $bridge->settings();
        
        if ($settings) {
            return $settings->setting('date_format');
        }
        
        return 'd/m/Y'; // Default fallback
    }
}
```

This approach:
- ✅ Removes CodeIgniter artifacts from helper classes
- ✅ Centralizes legacy dependencies in one place
- ✅ Makes migration path clear and trackable
- ✅ Provides null safety when CI isn't available

See `Modules/Core/Services/README.md` for more details on LegacyBridge.

## Helper Classes

### Core Helpers

- **DateHelper** - Date formatting and manipulation
  - `dateFormats()` - Available date formats
  - `dateFromMysql()` - Convert MySQL date to user format
  - `dateToMysql()` - Convert user date to MySQL format
  - `dateFormatSetting()` - Get date format setting
  - And more...

- **TranslationHelper** - Language translation and management
  - `trans()` - Translate a language string with fallback
  - `setLanguage()` - Set the application language
  - `getAvailableLanguages()` - Get list of available languages

- **SettingsHelper** - Application settings
  - `getSetting()` - Get a setting value
  - `getGatewaySettings()` - Get payment gateway settings
  - `checkSelect()` - Helper for select boxes

- **NumberHelper** - Number and currency formatting
  - `format_currency()` - Format amount as currency
  - `format_amount()` - Format amount
  - `standardize_amount()` - Standardize amount format
  - `round_tax()` - Round tax amounts

### Additional Helpers

- **ClientHelper** - Client-related functions
- **CountryHelper** - Country list and management
- **CustomValuesHelper** - Custom field values
- **DiacriticsHelper** - Handle diacritics in text
- **DropzoneHelper** - File upload helpers
- **EchoHelper** - Output helpers  
- **EInvoiceHelper** - Electronic invoice generation
- **InvoiceHelper** - Invoice-specific helpers
- **JsonErrorHelper** - JSON error handling
- **MailerHelper** - Email sending
- **PdfHelper** - PDF generation
- **RedirectHelper** - Redirection helpers
- **TemplateHelper** - Template rendering
- **UserHelper** - User-related functions

## Usage

### Modern Approach (Recommended)

```php
use Modules\Core\Support\DateHelper;
use Modules\Core\Support\NumberHelper;

// Use static methods directly
$formats = DateHelper::dateFormats();
$date = DateHelper::dateFromMysql('2024-01-15');
$amount = NumberHelper::format_currency(1234.56);
```

### Legacy Approach (Backward Compatible)

```php
// Procedural functions still work via bc_helper.php
$formats = date_formats();
$date = date_from_mysql('2024-01-15');
$amount = format_currency(1234.56);
```

## Backward Compatibility

The `bc_helper.php` file in `Modules/Core/Helpers/` provides procedural function wrappers that call the static methods. This ensures that all existing code continues to work without modification.

## Adding New Helpers

1. Create a new class in this directory (e.g., `MyHelper.php`)
2. Add your static methods
3. Update `bc_helper.php` to add procedural function wrappers
4. Update this README

Example:

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Support;

class MyHelper
{
    public static function myFunction(string $param): string
    {
        // Your implementation
        return $param;
    }
}
```

Then in `bc_helper.php`:

```php
if (!function_exists('my_function')) {
    function my_function(string $param): string {
        return MyHelper::myFunction($param);
    }
}
```

## Testing

Helper classes can be tested using PHPUnit:

```php
use Modules\Core\Support\DateHelper;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{
    public function test_date_formats_returns_array()
    {
        $formats = DateHelper::dateFormats();
        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats);
    }
}
```

## Migration Status

✅ **Fully Migrated (ALL HELPERS COMPLETE):**
- ClientHelper ✅
- CountryHelper ✅
- CustomValuesHelper ✅
- DateHelper ✅
- DiacriticsHelper ✅
- DropzoneHelper ✅
- EchoHelper ✅ (Added missing `htmlsc()` method)
- EInvoiceHelper ✅
- InvoiceHelper ✅
- JsonErrorHelper ✅
- MailerHelper ✅ (Note: `email_invoice` and `email_quote` in mailer_helper.php)
- MpdfHelper ✅
- NumberHelper ✅
- OrphanHelper ✅
- PagerHelper ✅
- PaymentsHelper ✅
- PdfHelper ✅
- RedirectHelper ✅
- SettingsHelper ✅
- TemplateHelper ✅
- TranslationHelper ✅
- UserHelper ✅

**Migration Complete:** All 22 helper categories have been migrated to static classes in `Modules/Core/Support/` with backward-compatible wrappers in `bc_helper.php`.

**Removed Files:** All old procedural helper files from `Modules/Core/Helpers/` have been removed, including:
- All `*_helper.php` files (except `mailer_helper.php` for complex email functions)
- `helpers.php` autoloader
- `XMLconfigs/` directory
- `country-list/` directory

**Exception:** `mailer_helper.php` is retained temporarily for `email_invoice()` and `email_quote()` which still use CodeIgniter dependencies and will be migrated to Laravel Mail in a future update.
