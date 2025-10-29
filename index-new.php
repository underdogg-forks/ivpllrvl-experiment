<?php

/**
 * InvoicePlane - Modern Bootstrap
 * 
 * This is the new bootstrap file that initializes the Illuminate application.
 * During the migration period, this works alongside the legacy CodeIgniter bootstrap.
 */

// Load Composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Load environment configuration
if (!file_exists('ipconfig.php')) {
    exit('The <b>ipconfig.php</b> file is missing! Please make a copy of the <b>ipconfig.php.example</b> file and rename it to <b>ipconfig.php</b>');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'ipconfig.php');
$dotenv->load();

// Helper functions for environment variables
if (!function_exists('env')) {
    function env($env_key, $default = null)
    {
        if (isset($_ENV[$env_key])) {
            return $_ENV[$env_key];
        }
        return $default;
    }
}

if (!function_exists('env_bool')) {
    function env_bool($env_key, $default = 'false'): bool
    {
        return env($env_key, $default) === 'true';
    }
}

// Define constants
define('IP_DEBUG', env_bool('ENABLE_DEBUG'));
define('SUMEX_SETTINGS', env_bool('SUMEX_SETTINGS'));
define('SUMEX_URL', env('SUMEX_URL'));
define('ENVIRONMENT', $_SERVER['CI_ENV'] ?? 'development');

// Set error reporting based on environment
switch (ENVIRONMENT) {
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'The application environment is not set correctly.';
        exit(1);
}

// Define path constants
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('BASEPATH', FCPATH . 'vendor/codeigniter/framework/system' . DIRECTORY_SEPARATOR);
define('APPPATH', FCPATH . 'application' . DIRECTORY_SEPARATOR);
define('VIEWPATH', APPPATH . 'views' . DIRECTORY_SEPARATOR);
define('IPCONFIG_FILE', FCPATH . 'ipconfig.php');
define('LOGS_FOLDER', APPPATH . 'logs' . DIRECTORY_SEPARATOR);
define('UPLOADS_FOLDER', FCPATH . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOADS_ARCHIVE_FOLDER', UPLOADS_FOLDER . 'archive' . DIRECTORY_SEPARATOR);
define('UPLOADS_CFILES_FOLDER', UPLOADS_FOLDER . 'customer_files' . DIRECTORY_SEPARATOR);
define('UPLOADS_TEMP_FOLDER', UPLOADS_FOLDER . 'temp' . DIRECTORY_SEPARATOR);
define('UPLOADS_TEMP_MPDF_FOLDER', UPLOADS_TEMP_FOLDER . 'mpdf' . DIRECTORY_SEPARATOR);
define('THEME_FOLDER', FCPATH . 'assets' . DIRECTORY_SEPARATOR);

// Automatic temp pdf & xml files cleanup
$files = array_merge(
    glob(UPLOADS_TEMP_FOLDER . '*.pdf') ?: [],
    glob(UPLOADS_TEMP_FOLDER . '*.xml') ?: []
);
array_map('unlink', $files);

// Bootstrap the Illuminate application
$app = require __DIR__ . '/bootstrap/app.php';

// Register module service providers
if (file_exists(__DIR__ . '/storage/modules_statuses.json')) {
    $modulesStatuses = json_decode(file_get_contents(__DIR__ . '/storage/modules_statuses.json'), true);
    
    $modules = [
        'Core' => 'Modules\\Core\\Providers\\CoreServiceProvider',
        'Invoices' => 'Modules\\Invoices\\Providers\\InvoicesServiceProvider',
        'Payments' => 'Modules\\Payments\\Providers\\PaymentsServiceProvider',
        'Products' => 'Modules\\Products\\Providers\\ProductsServiceProvider',
        'Quotes' => 'Modules\\Quotes\\Providers\\QuotesServiceProvider',
        'Crm' => 'Modules\\Crm\\Providers\\CrmServiceProvider',
        'Users' => 'Modules\\Users\\Providers\\UsersServiceProvider',
        'Custom' => 'Modules\\Custom\\Providers\\CustomServiceProvider',
    ];
    
    foreach ($modules as $module => $provider) {
        if (!isset($modulesStatuses[$module]) || $modulesStatuses[$module] === true) {
            $app->register(new $provider($app));
        }
    }
}

// For now, we'll continue to use the legacy CodeIgniter bootstrap
// Once migration is complete, we'll replace this with proper routing

// Check if we should use new routes (for migrated modules)
$useNewBootstrap = env_bool('USE_NEW_BOOTSTRAP', 'false');

if ($useNewBootstrap) {
    // TODO: Implement new routing system here
    // This will be activated once controllers are migrated
    echo "New Illuminate bootstrap is not yet fully implemented. Set USE_NEW_BOOTSTRAP=false in ipconfig.php";
    exit;
} else {
    // Use legacy CodeIgniter bootstrap
    require_once BASEPATH . 'core/CodeIgniter.php';
}
