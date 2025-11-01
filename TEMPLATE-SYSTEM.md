# Template System Configuration

## Overview

InvoicePlane uses **plain PHP templates**, not Blade templates. This document explains the configuration and usage of the template system.

## Configuration

### AppServiceProvider

The template system is configured in `app/Providers/AppServiceProvider.php`:

```php
public function register(): void
{
    // Configure view engine to use PHP as the primary template compiler
    $this->app->singleton('view.engine.resolver', function ($app) {
        $resolver = new \Illuminate\View\Engines\EngineResolver();

        // Register PHP engine FIRST (for plain PHP templates - primary engine)
        $resolver->register('php', function () {
            return new PhpEngine();
        });

        // Register Blade engine as secondary (for potential future use)
        $resolver->register('blade', function () use ($app) {
            $compiler = new BladeCompiler(
                $app['files'],
                $app['config']['view.compiled'] ?? storage_path('framework/views')
            );

            return new CompilerEngine($compiler);
        });

        return $resolver;
    });
}
```

### Why PHP Templates?

1. **Consistency**: All existing views (169 files) already use plain PHP
2. **Simplicity**: No need to learn Blade syntax
3. **Migration**: Easier migration from CodeIgniter (which also uses PHP templates)
4. **Performance**: No compilation step for simple PHP templates
5. **Flexibility**: Blade is still available if needed in the future

## File Extensions

- **Use**: `.php` extension for all view files
- **Avoid**: `.blade.php` extension

### Examples

✅ **Correct:**
```
Modules/Invoices/Resources/views/index.php
Modules/Quotes/resources/views/view.php
resources/views/welcome.php
```

❌ **Incorrect:**
```
Modules/Invoices/Resources/views/index.blade.php
resources/views/welcome.blade.php
```

## Usage

### Rendering Views

```php
// In controllers
return view('module::view_name', ['data' => $value]);

// In routes
Route::get('/', function () {
    return view('welcome');
});
```

### View Syntax

Use standard PHP syntax in view files:

```php
<!-- resources/views/example.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($heading); ?></h1>
    
    <?php if ($showContent): ?>
        <div class="content">
            <?php echo $content; ?>
        </div>
    <?php endif; ?>
    
    <?php foreach ($items as $item): ?>
        <div class="item">
            <?php echo htmlspecialchars($item['name']); ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
```

## Module Configuration

The module generator is configured in `config/modules.php` to create `.php` view files:

```php
'stubs' => [
    'files' => [
        'views/index'  => 'Resources/views/index.php',
        'views/master' => 'Resources/views/layouts/master.php',
        // ...
    ],
],
```

## Testing

Tests verify the PHP template system is correctly configured:

```bash
# Run template system tests
vendor/bin/phpunit tests/Feature/ViewTemplateSystemTest.php
```

## Migration Notes

When migrating from CodeIgniter to Laravel:

1. **View loading**: 
   - OLD: `$this->load->view('view_name', $data);`
   - NEW: `return view('module::view_name', $data);`

2. **File location**:
   - OLD: `application/modules/ModuleName/views/view_name.php`
   - NEW: `Modules/ModuleName/Resources/views/view_name.php`

3. **Syntax**: No changes needed - both use plain PHP

## References

- [Laravel Views Documentation](https://laravel.com/docs/views)
- [Illuminate View Component](https://github.com/illuminate/view)
- Project Architecture: `.github/copilot-instructions.md`
