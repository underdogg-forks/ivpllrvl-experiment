<?php

/**
 * InvoicePlane Bootstrap Helper Loader
 * 
 * This file loads the backward compatibility helper which in turn
 * loads all helper functions from Modules/Core/Helpers/
 * 
 * Place this in application/config/autoload.php or include it early in the bootstrap
 */

// Load the backward compatibility helper
$bc_helper_path = dirname(dirname(__DIR__)) . '/Modules/Core/Helpers/bc_helper.php';

if (file_exists($bc_helper_path)) {
    require_once $bc_helper_path;
}
