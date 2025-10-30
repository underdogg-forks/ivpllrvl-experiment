# Services - Modules/Core/Services

This directory contains service classes that provide clean interfaces to application functionality.

## LegacyBridge

**Purpose:** Temporary bridge to access CodeIgniter components during the migration from CodeIgniter to Laravel.

**Status:** 🚨 **DEPRECATED** - This class exists only for backward compatibility and will be removed once the migration is complete.

### Why LegacyBridge?

The user specifically requested that CodeIgniter artifacts like `$CI = &get_instance()` be removed from the codebase. The LegacyBridge pattern centralizes all CodeIgniter dependencies in one place, making them easier to track and remove later.

### Before (Unacceptable):

```php
class SomeHelper
{
    public static function doSomething()
    {
        $CI = &get_instance();  // ❌ CodeIgniter artifact
        return $CI->mdl_settings->setting('key');
    }
}
```

### After (Clean):

```php
use Modules\Core\Services\LegacyBridge;

class SomeHelper
{
    public static function doSomething()
    {
        $bridge = LegacyBridge::getInstance();  // ✅ Clean interface
        $settings = $bridge->settings();
        return $settings ? $settings->setting('key') : null;
    }
}
```

### Benefits

1. **Single Point of Contact** - All CodeIgniter access goes through one class
2. **Easy to Remove** - When migration is complete, just delete LegacyBridge
3. **Clean Helper Code** - Helper classes don't directly reference CodeIgniter
4. **Null Safety** - Bridge methods check if CodeIgniter is available
5. **Searchable** - Easy to find all legacy dependencies with grep

### Available Methods

```php
$bridge = LegacyBridge::getInstance();

// Access CodeIgniter components
$settings = $bridge->settings();     // mdl_settings model
$lang = $bridge->lang();             // Language instance
$session = $bridge->session();       // Session instance  
$config = $bridge->config();         // Config instance

// Utility methods
$bridge->loadHelper('directory');    // Load a CI helper
$available = $bridge->isAvailable(); // Check if CI is loaded
$ci = $bridge->getRawInstance();     // Get raw CI instance (use sparingly)
```

### Migration Path

As the application migrates from CodeIgniter to Laravel:

1. **Phase 1** (Current): Use LegacyBridge to access CI components
2. **Phase 2**: Create Laravel service classes that implement the same interface
3. **Phase 3**: Update helpers to use Laravel services instead of LegacyBridge
4. **Phase 4**: Delete LegacyBridge once all dependencies are migrated

### Example Migration

```php
// Phase 1: Use LegacyBridge
$settings = LegacyBridge::getInstance()->settings();
$value = $settings->setting('key');

// Phase 2: Create SettingsRepository (Laravel service)
class SettingsRepository {
    public function get($key) {
        // Use Laravel's config or database
        return config("app_settings.{$key}");
    }
}

// Phase 3: Use Laravel service in helpers
$value = app(SettingsRepository::class)->get('key');

// Phase 4: Delete LegacyBridge ✅
```

## SettingsRepository

**Purpose:** Provide a clean interface to access application settings.

**Status:** ⏳ Work in Progress

This will eventually replace LegacyBridge for settings access once settings are migrated to Laravel's config system or database.

## Future Services

As the migration progresses, add new service classes here:

- `TranslationService` - Replace CodeIgniter language system
- `SessionService` - Replace CodeIgniter sessions with Laravel sessions
- `CacheService` - Replace CodeIgniter cache with Laravel cache
- etc.

Each service should:
- Have a clear, single responsibility
- Provide a clean, framework-agnostic interface
- Be easy to test
- Document its purpose and usage
