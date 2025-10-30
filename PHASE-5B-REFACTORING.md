# InvoicePlane Phase 5B - Infrastructure Refactoring

## Changes Made

This refactoring addresses the second round of Phase 5 feedback, reorganizing application structure to follow Laravel conventions.

### Directory Restructuring

#### 1. Helpers → `Modules/Core/Helpers/`
- All helper files moved from `application/helpers/` to `Modules/Core/Helpers/`
- Created `bc_helper.php` for backward compatibility - autoloaded to provide all helper functions
- Future: Convert procedural helpers to static class methods

#### 2. Hooks → `Modules/Core/Hooks/`
- Moved `application/hooks/` to `Modules/Core/Hooks/`
- Maintains CodeIgniter hook structure but within module organization

#### 3. Config → `config/` (root)
- Moved `application/config/` to root `config/` directory
- Updated cache path: `storage/cache/`
- Updated logs path: `storage/logs/`
- Laravel-style convention for configuration

#### 4. Assets Restructuring
**Source Assets:** `resources/assets/`
- Contains SCSS source files
- JavaScript source files
- All asset sources for development

**Compiled Assets:** `public/assets/`
- Build output directory
- Contains compiled/minified CSS and JS
- Fonts, images, and other static assets

#### 5. Build System - Vite
**Created:** `vite.config.js`
- Modern replacement for `Gruntfile.js`
- Features:
  - SCSS compilation with autoprefixer
  - JavaScript bundling and minification
  - Hot module replacement for development
  - Automatic asset copying (fonts, locales)

**Entry Points:**
- `resources/assets/core/js/dependencies-entry.js` - Third-party libraries bundle
- `resources/assets/core/js/legacy-entry.js` - Polyfills for older browsers

#### 6. Storage → `storage/`
**Cache:** `storage/cache/`
- Laravel-style cache directory
- Configured in `config/config.php`

**Logs:** `storage/logs/`
- Laravel-style logs directory
- Configured in `config/config.php`

## New Structure

```
├── config/                      # Application configuration (moved from application/config/)
│   ├── autoload.php
│   ├── config.php              # Updated with storage/ paths
│   ├── database.php
│   ├── routes.php
│   └── ...
│
├── Modules/Core/
│   ├── Helpers/                # Moved from application/helpers/
│   │   ├── bc_helper.php       # Backward compatibility loader
│   │   ├── client_helper.php
│   │   ├── date_helper.php
│   │   └── ...
│   ├── Hooks/                  # Moved from application/hooks/
│   └── Libraries/              # From previous refactoring
│
├── resources/
│   └── assets/                 # Asset sources (SCSS, JS)
│       ├── core/
│       ├── invoiceplane/
│       └── invoiceplane_blue/
│
├── public/
│   ├── assets/                 # Compiled assets (output)
│   └── index.php
│
├── storage/
│   ├── cache/                  # Application cache
│   ├── logs/                   # Application logs
│   └── framework/
│
├── application/                # Minimal - only essential CI files
│   ├── controllers/
│   ├── core/
│   │   └── IP_Loader.php       # Loads bc_helper
│   ├── language/
│   ├── logs/                   # Kept for backward compatibility
│   └── third_party/
│
├── vite.config.js              # Modern build configuration
└── Gruntfile.js                # Legacy (can be removed after migration)
```

## Usage

### Development
```bash
# Install dependencies
npm install

# Development mode with hot reload
npm run dev

# Build for production
npm run build
```

### Backward Compatibility

The `bc_helper.php` file is automatically loaded and provides all helper functions in their original procedural form. This ensures existing code continues to work without modification.

### Configuration

All configuration files are now in the root `config/` directory, following Laravel conventions. The paths for cache and logs have been updated to use `storage/` directory.

### Assets

- **Development:** Edit files in `resources/assets/`
- **Production:** Built files output to `public/assets/`
- **Vite:** Handles compilation, minification, and bundling

## Migration Notes

1. **Helpers:** All helpers remain as procedural functions loaded via `bc_helper.php`
2. **Config:** Update any hard-coded paths to use new `config/` directory
3. **Cache/Logs:** Now use `storage/cache/` and `storage/logs/`
4. **Assets:** Development should use `resources/assets/`, production uses `public/assets/`
5. **Build:** Switch from `grunt` to `npm run build` or `npm run dev`

## Future Work

- Convert procedural helpers to static class methods
- Complete removal of legacy `application/` directory structure
- Migrate remaining CodeIgniter-specific code to Laravel/Illuminate
- Remove Gruntfile.js once Vite is fully tested
