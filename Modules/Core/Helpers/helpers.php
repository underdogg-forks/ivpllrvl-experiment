<?php

/**
 * Autoload all helper files from Modules/Core/Helpers directory
 */

$helpersDir = __DIR__;

// List of helper files to load
$helperFiles = [
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
foreach ($helperFiles as $file) {
    $filePath = $helpersDir . DIRECTORY_SEPARATOR . $file;
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}
