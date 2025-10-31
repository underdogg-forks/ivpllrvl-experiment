# Services - Modules/Core/Services

This directory contains service classes that provide clean interfaces to application functionality.

## Status

All CodeIgniter artifacts have been removed from the application. Helper classes now use pure Laravel code:

- **Settings**: Use `Modules\Core\Entities\Setting` Eloquent model
- **Translations**: Use Laravel's `__()` translation system  
- **Sessions**: Use Laravel's session() helper
- **Database**: Use Eloquent models in `Modules/*/Models/`

## Migration Complete

The LegacyBridge pattern has been **removed** as part of the complete migration to Laravel. All helper classes now use:

### Before (Deprecated - REMOVED):
```php
$bridge = LegacyBridge::getInstance();  // ❌ DELETED
$value = $bridge->settings()->setting('key');
```

### After (Current):
```php
use Modules\Core\Entities\Setting;

$value = Setting::getValue('key');  // ✅ Pure Laravel
```

## Available Models

The following Eloquent models are available for use:

**Core Models** (`Modules/Core/Models/`):
- `Setting` - Application settings with `getValue()`, `setValue()`, `getAllSettings()`
- `User` - User accounts and authentication
- `CustomField` - Custom field definitions
- `CustomValue` - Custom field values
- `EmailTemplate` - Email templates
- `Upload` - File uploads
- And more...

## Future Services

As features are added, create new service classes here:

- `MailService` - Email sending with Laravel Mail
- `PdfService` - PDF generation with Laravel views
- `InvoiceService` - Invoice business logic
- `PaymentService` - Payment processing
- etc.

Each service should:
- Have a clear, single responsibility
- Use Laravel's dependency injection
- Use Eloquent models for data access
- Be easy to test
- Document its purpose and usage

## Notes

Some helpers (PdfHelper, EInvoiceHelper) contain TODO markers where complex CodeIgniter code needs manual migration to Laravel patterns (views, libraries, etc.). These should be migrated as part of future development.
