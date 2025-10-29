<?php

/**
 * Backward Compatibility Helper
 * 
 * This file loads all legacy helper files to maintain backward compatibility
 * with existing CodeIgniter code that expects procedural helper functions.
 * 
 * All helpers have been moved to Modules/Core/Helpers/ but remain as
 * procedural functions for now. Future refactoring may convert these to
 * static class methods.
 *  
 * This file is autoloaded via config/autoload.php to ensure all helper
 * functions are available throughout the application.
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// Get the path to the Modules/Core/Helpers directory
$helpers_path = dirname(dirname(dirname(__DIR__))) . '/Modules/Core/Helpers/';

// List of all helper files to load
$helper_files = [
    'client_helper.php',
    'country_helper.php',
    'custom_values_helper.php',
    'date_helper.php',
    'diacritics_helper.php',
    'dropzone_helper.php',
    'e-invoice_helper.php',
    'echo_helper.php',
    'invoice_helper.php',
    'json_error_helper.php',
    'mailer_helper.php',
    'mpdf_helper.php',
    'number_helper.php',
    'orphan_helper.php',
    'pager_helper.php',
    'payments_helper.php',
    'pdf_helper.php',
    'redirect_helper.php',
    'settings_helper.php',
    'template_helper.php',
    'trans_helper.php',
    'user_helper.php',
];

// Load each helper file
foreach ($helper_files as $helper_file) {
    $file_path = $helpers_path . $helper_file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}
