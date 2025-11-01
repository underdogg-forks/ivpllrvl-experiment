# Laravel Storage Directory Setup - Summary

## Overview
This document describes the changes made to prepare the `storage` directory for full Laravel 12 compatibility, including moving the uploads directory into storage for better Laravel integration.

## Problem Statement
The application was being migrated to Laravel, but:
1. The storage directory was incomplete and did not match the standard Laravel structure
2. The `uploads/` directory was at the application root instead of inside `storage/`
3. This could cause issues with file storage operations, view compilation, testing, and cache management
4. Constants were being used instead of Laravel's modern helper functions

## Solution
1. Created all missing storage directories to match Laravel 12's expected structure
2. Moved the entire uploads directory structure into `storage/app/uploads/`
3. Created a Laravel filesystems configuration with multiple upload disks
4. Updated all path constants to point to the new location
5. Added modern helper functions to access upload paths

## Changes Made

### 1. New Directories Created
- `storage/app/` - Root directory for application-specific files
- `storage/app/public/` - Directory for publicly accessible files (can be symlinked to public/storage)
- `storage/app/uploads/` - **NEW** Main uploads directory (moved from root)
- `storage/app/uploads/archive/` - **NEW** Archived files
- `storage/app/uploads/customer_files/` - **NEW** Customer-uploaded files
- `storage/app/uploads/import/` - **NEW** Import files
- `storage/app/uploads/temp/` - **NEW** Temporary files
- `storage/app/uploads/temp/mpdf/` - **NEW** mPDF temporary files
- `storage/framework/testing/` - Directory for test-related temporary files
- `storage/framework/views/` - Directory for compiled Blade view cache

### 2. .gitignore Files Added/Updated
- `storage/app/.gitignore` - Ignores all files except public/ and uploads/ subdirectories
- `storage/app/public/.gitignore` - Ignores all user-uploaded files
- `storage/app/uploads/.gitignore` - **NEW** Ignores all uploaded files
- `storage/app/uploads/archive/.gitignore` - **NEW** Ignores archived files
- `storage/app/uploads/customer_files/.gitignore` - **NEW** Ignores customer files
- `storage/app/uploads/import/.gitignore` - **NEW** Ignores import files
- `storage/app/uploads/temp/.gitignore` - **NEW** Ignores temporary files
- `storage/framework/testing/.gitignore` - Ignores all test files
- `storage/framework/views/.gitignore` - Ignores all compiled views
- `storage/framework/sessions/.gitignore` - Fixed (was empty, now properly ignores session files)

### 3. Laravel Filesystem Configuration
Created `config/filesystems.php` with the following disks:
- `local` - Default local storage at `storage/app/`
- `public` - Public files at `storage/app/public/`
- `uploads` - Main uploads at `storage/app/uploads/`
- `uploads_archive` - Archive files at `storage/app/uploads/archive/`
- `uploads_customer_files` - Customer files at `storage/app/uploads/customer_files/`
- `uploads_import` - Import files at `storage/app/uploads/import/`
- `uploads_temp` - Temporary files at `storage/app/uploads/temp/`
- `uploads_temp_mpdf` - mPDF temp files at `storage/app/uploads/temp/mpdf/`

### 4. Updated Path Constants
Updated in `bootstrap/paths.php` and `bootstrap/helpers.php`:
- `UPLOADS_FOLDER` - Now points to `storage/app/uploads/` (was `uploads/`)
- `UPLOADS_ARCHIVE_FOLDER` - Now points to `storage/app/uploads/archive/`
- `UPLOADS_CFILES_FOLDER` - Now points to `storage/app/uploads/customer_files/`
- `UPLOADS_TEMP_FOLDER` - Now points to `storage/app/uploads/temp/`
- `UPLOADS_TEMP_MPDF_FOLDER` - Now points to `storage/app/uploads/temp/mpdf/`

### 5. New Helper Functions
Added modern Laravel-style helper functions in `bootstrap/helpers.php`:
- `uploads_path($path = '')` - Get path to uploads directory
- `uploads_archive_path($path = '')` - Get path to archive directory
- `uploads_customer_files_path($path = '')` - Get path to customer files directory
- `uploads_temp_path($path = '')` - Get path to temp directory
- `uploads_temp_mpdf_path($path = '')` - Get path to mPDF temp directory

### 6. Test Coverage Added
Extended `tests/Unit/StorageStructureTest.php` with 6 test methods:
1. `test_required_storage_directories_exist()` - Verifies all 15 required directories exist
2. `test_storage_directories_are_writable()` - Verifies all directories are writable
3. `test_gitignore_files_exist_in_storage_directories()` - Verifies .gitignore files exist
4. `test_gitignore_files_have_correct_content()` - Verifies .gitignore files have correct content
5. `test_upload_helper_functions()` - **NEW** Verifies helper functions return correct paths
6. `test_upload_constants_point_to_storage()` - **NEW** Verifies constants point to storage location

## Complete Storage Structure

```
storage/
├── app/                          # Application file storage
│   ├── .gitignore               # Ignores all except public/ and uploads/
│   ├── public/                  # Public file storage
│   │   └── .gitignore           # Ignores all files
│   └── uploads/                 # **NEW** Uploads (moved from root)
│       ├── .gitignore           # Ignores all files
│       ├── archive/             # Archived files
│       │   └── .gitignore
│       ├── customer_files/      # Customer-uploaded files
│       │   └── .gitignore
│       ├── import/              # Import files
│       │   └── .gitignore
│       └── temp/                # Temporary files
│           ├── .gitignore
│           └── mpdf/            # mPDF temporary files
├── framework/                   # Framework-specific files
│   ├── cache/                   # Cache storage
│   │   ├── .gitignore          # Ignores all except data/
│   │   └── data/               # Cache data
│   │       └── .gitignore      # Ignores all files
│   ├── sessions/               # Session storage
│   │   └── .gitignore          # Ignores all files (fixed)
│   ├── testing/                # Testing temporary files (new)
│   │   └── .gitignore          # Ignores all files
│   └── views/                  # Compiled views (new)
│       └── .gitignore          # Ignores all files
├── logs/                        # Application logs
│   └── .gitignore              # Ignores all except .gitignore
└── modules_statuses.json       # Module activation status
```

## Laravel Features Now Supported

### 1. File Storage (Storage Facade)
```php
// Store files in storage/app/
Storage::put('file.txt', 'contents');

// Store public files in storage/app/public/
Storage::disk('public')->put('avatar.jpg', $content);

// **NEW** Store uploaded files using specific disks
Storage::disk('uploads')->put('document.pdf', $content);
Storage::disk('uploads_customer_files')->put('invoice.pdf', $content);
Storage::disk('uploads_temp')->put('temp_file.tmp', $content);
```

### 2. Upload Path Helpers (Modern Approach)
```php
// Use new helper functions instead of constants
$path = uploads_path('document.pdf');
$archivePath = uploads_archive_path('old_file.pdf');
$customerPath = uploads_customer_files_path('invoice.pdf');
$tempPath = uploads_temp_path('processing.tmp');
$mpdfPath = uploads_temp_mpdf_path('temp.pdf');
```

### 3. Legacy Constants (Still Supported)
```php
// Old constants still work for backward compatibility
$path = UPLOADS_FOLDER . 'file.txt';
$archivePath = UPLOADS_ARCHIVE_FOLDER . 'archived.pdf';
$customerPath = UPLOADS_CFILES_FOLDER . 'customer_file.pdf';
$tempPath = UPLOADS_TEMP_FOLDER . 'temp.txt';
```

### 4. View Compilation
```php
// Blade views are automatically compiled to storage/framework/views/
view('welcome');
```

### 5. Session Storage
```php
// File-based sessions stored in storage/framework/sessions/
session(['key' => 'value']);
```

### 6. Cache Storage
```php
// File-based cache in storage/framework/cache/
Cache::put('key', 'value', $seconds);
```

### 7. Testing
```php
// Test files stored in storage/framework/testing/
// Used during feature and integration tests
```

## Verification

All checks passed ✅:
- All 15 required directories exist (8 framework + 7 uploads)
- All directories are writable (755 permissions)
- All .gitignore files are in place (13 total)
- All .gitignore files have correct content
- Helper functions return correct paths
- Constants point to storage location

## Migration from Old uploads/ Directory

### Automatic Path Updates
The following constants have been automatically updated:
- `UPLOADS_FOLDER` - Changed from `uploads/` to `storage/app/uploads/`
- All subdirectory constants updated accordingly

### Recommended Migration Steps
1. **Copy existing uploads** (if any exist in old location):
   ```bash
   # Backup old uploads
   cp -r uploads/* storage/app/uploads/
   ```

2. **Verify uploads work**:
   ```bash
   # Test that helper functions return correct paths
   php -r "require 'bootstrap/helpers.php'; echo uploads_path() . PHP_EOL;"
   ```

3. **Update application code** (recommended):
   Replace constant usage with helper functions:
   ```php
   // Old (still works)
   $path = UPLOADS_FOLDER . 'file.txt';
   
   // New (preferred)
   $path = uploads_path('file.txt');
   ```

4. **Remove old uploads directory** (after verification):
   ```bash
   # Only after confirming all uploads are working from new location
   rm -rf uploads/
   ```

## Next Steps for Laravel Integration

### 1. Create Public Storage Symlink
```bash
php artisan storage:link
```
This creates a symbolic link from `public/storage` to `storage/app/public`, making files publicly accessible.

### 2. Clear Existing Caches
```bash
php artisan cache:clear    # Clear application cache
php artisan view:clear     # Clear compiled views
php artisan config:clear   # Clear configuration cache
```

### 3. Configure Storage Disk (if needed)
In `config/filesystems.php`, ensure the disks are properly configured:
```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

## File Permissions

All storage directories have 755 permissions (rwxr-xr-x), which is:
- Readable and executable by all
- Writable by owner only
- Standard for Laravel storage directories

If you need different permissions (e.g., for web server), you can adjust:
```bash
chmod -R 775 storage
chown -R www-data:www-data storage  # For Apache/Nginx
```

## Git Behavior

The .gitignore files ensure that:
1. User-uploaded files are not committed
2. Compiled views are not committed
3. Cache files are not committed
4. Session files are not committed
5. Test files are not committed
6. Log files are not committed (except .gitignore itself)
7. Directory structure is preserved in git

## Testing

Run the storage structure tests:
```bash
vendor/bin/phpunit tests/Unit/StorageStructureTest.php
```

## Conclusion

The storage directory is now fully prepared for Laravel 12 and matches the standard Laravel directory structure. All Laravel storage-dependent features (file storage, views, cache, sessions, testing) can now operate correctly.
