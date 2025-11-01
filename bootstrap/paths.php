<?php

/**
 * Application Paths Configuration.
 *
 * This file defines all path constants used throughout the application.
 * Separated from index.php for cleaner organization.
 */

/*
|--------------------------------------------------------------------------
| Base Paths
|--------------------------------------------------------------------------
|
| Define the base directories for the application structure.
|
*/

// Path to the front controller (public) directory
define('FCPATH', __DIR__ . '/../public' . DIRECTORY_SEPARATOR);

// Path to the base of the application
define('BASEPATH', base_path('vendor/codeigniter/framework/system') . DIRECTORY_SEPARATOR);

// Path to the application directory (legacy CodeIgniter)
define('APPPATH', base_path('application') . DIRECTORY_SEPARATOR);

// Name of the system directory
define('SYSDIR', 'system');

// Name of THIS file
define('SELF', 'index.php');

/*
|--------------------------------------------------------------------------
| View Paths
|--------------------------------------------------------------------------
|
| Paths for view files and templates.
|
*/

// Legacy view path (for backward compatibility)
define('VIEWPATH', APPPATH . 'views' . DIRECTORY_SEPARATOR);

/*
|--------------------------------------------------------------------------
| Configuration Paths
|--------------------------------------------------------------------------
|
| Paths for configuration files.
|
*/

// Environment configuration file
define('IPCONFIG_FILE', base_path('.env'));

/*
|--------------------------------------------------------------------------
| Storage Paths
|--------------------------------------------------------------------------
|
| Paths for logs, cache, and other storage.
|
*/

// Logs directory
define('LOGS_FOLDER', storage_path('logs') . DIRECTORY_SEPARATOR);

// Cache directory
define('CACHE_FOLDER', storage_path('cache') . DIRECTORY_SEPARATOR);

/*
|--------------------------------------------------------------------------
| Upload Paths
|--------------------------------------------------------------------------
|
| Paths for file uploads and temporary files.
| Now located in storage/app/uploads for Laravel compatibility.
|
*/

// Main uploads directory (now in storage)
define('UPLOADS_FOLDER', storage_path('app/uploads') . DIRECTORY_SEPARATOR);

// Upload subdirectories
define('UPLOADS_ARCHIVE_FOLDER', UPLOADS_FOLDER . 'archive' . DIRECTORY_SEPARATOR);
define('UPLOADS_CFILES_FOLDER', UPLOADS_FOLDER . 'customer_files' . DIRECTORY_SEPARATOR);
define('UPLOADS_TEMP_FOLDER', UPLOADS_FOLDER . 'temp' . DIRECTORY_SEPARATOR);
define('UPLOADS_TEMP_MPDF_FOLDER', UPLOADS_TEMP_FOLDER . 'mpdf' . DIRECTORY_SEPARATOR);

/*
|--------------------------------------------------------------------------
| Asset Paths
|--------------------------------------------------------------------------
|
| Paths for themes, assets, and static files.
|
*/

// Theme/assets directory
define('THEME_FOLDER', base_path('public/assets') . DIRECTORY_SEPARATOR);

/*
|--------------------------------------------------------------------------
| Module Paths
|--------------------------------------------------------------------------
|
| Paths for PSR-4 modules.
|
*/

// Modules base directory
define('MODULES_PATH', base_path('Modules') . DIRECTORY_SEPARATOR);

/*
|--------------------------------------------------------------------------
| Resource Paths
|--------------------------------------------------------------------------
|
| Paths for resources (views, assets, etc).
|
*/

// Resources directory
define('RESOURCES_PATH', base_path('resources') . DIRECTORY_SEPARATOR);
