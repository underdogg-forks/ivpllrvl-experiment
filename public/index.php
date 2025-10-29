<?php

/**
 * InvoicePlane - Application Entry Point
 * 
 * Modern Laravel-based front controller replacing legacy CodeIgniter bootstrap.
 * This file initializes the Illuminate container and handles all incoming requests.
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it!
|
*/

require __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Load Environment Variables
|--------------------------------------------------------------------------
|
| Load environment configuration from .env file using phpdotenv.
|
*/

if (!file_exists(__DIR__ . '/../.env')) {
    exit('The <b>.env</b> file is missing! Please copy <b>.env.example</b> to <b>.env</b> and configure your settings.');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

/*
|--------------------------------------------------------------------------
| Application Environment & Debugging
|--------------------------------------------------------------------------
|
| Configure error reporting and debugging based on the environment.
| This section controls how errors are displayed and logged.
|
*/

define('ENVIRONMENT', env('CI_ENV', 'development'));

switch (ENVIRONMENT) {
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', '1');
        break;

    case 'testing':
    case 'production':
        ini_set('display_errors', '0');
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'The application environment is not set correctly.';
        exit(1);
}

/*
|--------------------------------------------------------------------------
| Application Constants
|--------------------------------------------------------------------------
|
| Define application-specific constants used throughout the application.
|
*/

define('IP_DEBUG', env_bool('ENABLE_DEBUG'));
define('SUMEX_SETTINGS', env_bool('SUMEX_SETTINGS'));
define('SUMEX_URL', env('SUMEX_URL', ''));

/*
|--------------------------------------------------------------------------
| Load Path Helpers
|--------------------------------------------------------------------------
|
| Load helper functions for path resolution.
|
*/

require __DIR__ . '/../bootstrap/helpers.php';

/*
|--------------------------------------------------------------------------
| Load Application Paths
|--------------------------------------------------------------------------
|
| Define all application paths in a separate configuration file.
|
*/

require __DIR__ . '/../bootstrap/paths.php';

/*
|--------------------------------------------------------------------------
| Bootstrap The Application
|--------------------------------------------------------------------------
|
| Bootstrap the Illuminate application and get the container instance.
| This replaces the legacy CodeIgniter bootstrap.
|
*/

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    /*
    |--------------------------------------------------------------------------
    | Run The Application
    |--------------------------------------------------------------------------
    |
    | Once we have the application, we can handle the incoming request using
    | the application's HTTP kernel. Then we can send the response back to
    | the client's browser, allowing them to enjoy our application.
    |
    */
    
    // Clean up temporary files
    if (defined('UPLOADS_TEMP_FOLDER')) {
        $tempFiles = array_merge(
            glob(UPLOADS_TEMP_FOLDER . '*.pdf') ?: [],
            glob(UPLOADS_TEMP_FOLDER . '*.xml') ?: []
        );
        array_map('unlink', $tempFiles);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Laravel Routing
    |--------------------------------------------------------------------------
    |
    | CodeIgniter has been removed. All routing is now handled by Laravel.
    | Basic routing implementation below - to be expanded as needed.
    |
    */
    
    // Get the request URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $requestUri = parse_url($requestUri, PHP_URL_PATH);
    
    // Remove base path if exists
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    if ($basePath !== '/') {
        $requestUri = substr($requestUri, strlen($basePath));
    }
    
    // Trim slashes
    $requestUri = trim($requestUri, '/');
    
    // Basic routing - to be expanded with proper Laravel routing
    switch ($requestUri) {
        case '':
        case 'index.php':
            echo view('core::welcome')->render();
            break;
            
        default:
            http_response_code(404);
            echo '<h1>404 - Not Found</h1>';
            echo '<p>The requested page was not found.</p>';
            echo '<p><small>Route: ' . htmlspecialchars($requestUri) . '</small></p>';
            echo '<p><a href="/">Go to Home</a></p>';
    }
    
} catch (\Exception $e) {
    /*
    |--------------------------------------------------------------------------
    | Exception Handling
    |--------------------------------------------------------------------------
    |
    | If an exception occurs during bootstrap, display a friendly error
    | message and log the exception for debugging.
    |
    */
    
    if (ENVIRONMENT === 'development') {
        echo '<h1>Application Error</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        error_log('Application Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>An error occurred. Please try again later.</p>';
    }
    
    exit(1);
    
} finally {
    /*
    |--------------------------------------------------------------------------
    | Cleanup & Shutdown
    |--------------------------------------------------------------------------
    |
    | Perform any necessary cleanup operations before the script terminates.
    |
    */
    
    // Flush output buffers if needed
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}
