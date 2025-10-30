<?php

/**
 * Helper functions for the application
 */

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        return __DIR__ . '/../config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return __DIR__ . '/..' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        return __DIR__ . '/../storage' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function app_path($path = '')
    {
        return __DIR__ . '/../app' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('module_path')) {
    /**
     * Get the path to a module.
     *
     * @param  string  $module
     * @param  string  $path
     * @return string
     */
    function module_path($module = '', $path = '')
    {
        $base = __DIR__ . '/../Modules';
        
        if (! $module) {
            return $base;
        }
        
        return $base . DIRECTORY_SEPARATOR . $module . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string|null  $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app('view');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $abstract
     * @param  array  $parameters
     * @return mixed|\Illuminate\Contracts\Container\Container
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return \Illuminate\Container\Container::getInstance();
        }

        return \Illuminate\Container\Container::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed  ...$args
     * @return void
     */
    function dd(...$args)
    {
        foreach ($args as $x) {
            var_dump($x);
        }

        die(1);
    }
}

if (! function_exists('env')) {
    /**
     * Get the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        return $default;
    }
}

if (! function_exists('env_bool')) {
    /**
     * Get the value of an environment variable as a boolean.
     *
     * @param  string  $key
     * @param  string  $default
     * @return bool
     */
    function env_bool($key, $default = 'false'): bool
    {
        return env($key, $default) === 'true';
    }
}

// Define constants that may be needed by legacy code
if (!defined('IP_DEBUG')) {
    define('IP_DEBUG', env_bool('ENABLE_DEBUG', 'false'));
}
if (!defined('SUMEX_SETTINGS')) {
    define('SUMEX_SETTINGS', env_bool('SUMEX_SETTINGS', 'false'));
}
if (!defined('SUMEX_URL')) {
    define('SUMEX_URL', env('SUMEX_URL', ''));
}

// Define path constants if not already defined
if (!defined('FCPATH')) {
    define('FCPATH', dirname(__DIR__) . '/public' . DIRECTORY_SEPARATOR);
}
if (!defined('BASEPATH')) {
    // For Laravel, we don't need CodeIgniter system path, but legacy code might reference it
    define('BASEPATH', dirname(__DIR__) . '/vendor/codeigniter/framework/system' . DIRECTORY_SEPARATOR);
}
if (!defined('APPPATH')) {
    define('APPPATH', dirname(__DIR__) . '/application' . DIRECTORY_SEPARATOR);
}
if (!defined('VIEWPATH')) {
    define('VIEWPATH', dirname(__DIR__) . '/application/views' . DIRECTORY_SEPARATOR);
}
if (!defined('SELF')) {
    define('SELF', 'index.php');
}
if (!defined('SYSDIR')) {
    define('SYSDIR', 'system');
}

// Upload paths
if (!defined('UPLOADS_FOLDER')) {
    define('UPLOADS_FOLDER', dirname(__DIR__) . '/uploads' . DIRECTORY_SEPARATOR);
}
if (!defined('UPLOADS_ARCHIVE_FOLDER')) {
    define('UPLOADS_ARCHIVE_FOLDER', UPLOADS_FOLDER . 'archive' . DIRECTORY_SEPARATOR);
}
if (!defined('UPLOADS_CFILES_FOLDER')) {
    define('UPLOADS_CFILES_FOLDER', UPLOADS_FOLDER . 'customer_files' . DIRECTORY_SEPARATOR);
}
if (!defined('UPLOADS_TEMP_FOLDER')) {
    define('UPLOADS_TEMP_FOLDER', UPLOADS_FOLDER . 'temp' . DIRECTORY_SEPARATOR);
}
if (!defined('UPLOADS_TEMP_MPDF_FOLDER')) {
    define('UPLOADS_TEMP_MPDF_FOLDER', UPLOADS_TEMP_FOLDER . 'mpdf' . DIRECTORY_SEPARATOR);
}

// Other paths
if (!defined('THEME_FOLDER')) {
    define('THEME_FOLDER', dirname(__DIR__) . '/public/assets' . DIRECTORY_SEPARATOR);
}
if (!defined('MODULES_PATH')) {
    define('MODULES_PATH', dirname(__DIR__) . '/Modules' . DIRECTORY_SEPARATOR);
}
if (!defined('RESOURCES_PATH')) {
    define('RESOURCES_PATH', dirname(__DIR__) . '/resources' . DIRECTORY_SEPARATOR);
}
if (!defined('LOGS_FOLDER')) {
    define('LOGS_FOLDER', dirname(__DIR__) . '/storage/logs' . DIRECTORY_SEPARATOR);
}
if (!defined('CACHE_FOLDER')) {
    define('CACHE_FOLDER', dirname(__DIR__) . '/storage/cache' . DIRECTORY_SEPARATOR);
}
