<?php

/**
 * Backward Compatibility Helper.
 *
 * This file provides procedural function wrappers that call static methods
 * from helper classes in Modules/Core/Support/. This maintains backward
 * compatibility with existing CodeIgniter code that expects procedural
 * helper functions while moving towards a more modern, object-oriented approach.
 *
 * All helper logic is now in static class methods, and these functions
 * simply delegate to those methods.
 *
 * This file is autoloaded via composer.json to ensure all helper
 * functions are available throughout the application.
 */

use Modules\Core\Support\DateHelper;
use Modules\Core\Support\NumberHelper;
use Modules\Core\Support\SettingsHelper;
use Modules\Core\Support\TranslationHelper;

// ============================================================================
// Date Helper Functions
// ============================================================================

if ( ! function_exists('date_formats')) {
    function date_formats(): array
    {
        return DateHelper::dateFormats();
    }
}

if ( ! function_exists('date_from_mysql')) {
    function date_from_mysql($date, $ignore_post_check = false)
    {
        return DateHelper::dateFromMysql($date, $ignore_post_check);
    }
}

if ( ! function_exists('date_from_timestamp')) {
    function date_from_timestamp($timestamp): string
    {
        return DateHelper::dateFromTimestamp($timestamp);
    }
}

if ( ! function_exists('date_to_mysql')) {
    function date_to_mysql($date)
    {
        return DateHelper::dateToMysql($date);
    }
}

if ( ! function_exists('is_date')) {
    function is_date($date): bool
    {
        return DateHelper::isDate($date);
    }
}

if ( ! function_exists('date_format_setting')) {
    function date_format_setting()
    {
        return DateHelper::dateFormatSetting();
    }
}

if ( ! function_exists('date_format_datepicker')) {
    function date_format_datepicker()
    {
        return DateHelper::dateFormatDatepicker();
    }
}

if ( ! function_exists('increment_user_date')) {
    function increment_user_date($date, string $increment): string
    {
        return DateHelper::incrementUserDate($date, $increment);
    }
}

if ( ! function_exists('increment_date')) {
    function increment_date($date, string $increment): string
    {
        return DateHelper::incrementDate($date, $increment);
    }
}

// ============================================================================
// Translation Helper Functions
// ============================================================================

if ( ! function_exists('trans')) {
    function trans($line, ?string $id = '', $default = null)
    {
        return TranslationHelper::trans($line, $id, $default);
    }
}

if ( ! function_exists('set_language')) {
    function set_language($language): void
    {
        TranslationHelper::setLanguage($language);
    }
}

if ( ! function_exists('get_available_languages')) {
    function get_available_languages()
    {
        return TranslationHelper::getAvailableLanguages();
    }
}

// ============================================================================
// Settings Helper Functions
// ============================================================================

if ( ! function_exists('get_setting')) {
    function get_setting($setting_key, $default = '', $escape = false)
    {
        return SettingsHelper::getSetting($setting_key, $default, $escape);
    }
}

if ( ! function_exists('get_gateway_settings')) {
    function get_gateway_settings($gateway)
    {
        return SettingsHelper::getGatewaySettings($gateway);
    }
}

if ( ! function_exists('check_select')) {
    function check_select($value1, $value2 = null, $operator = '==', $checked = false): void
    {
        SettingsHelper::checkSelect($value1, $value2, $operator, $checked);
    }
}

// ============================================================================
// Number Helper Functions
// ============================================================================

if ( ! function_exists('format_currency')) {
    function format_currency($amount): string
    {
        return NumberHelper::format_currency($amount);
    }
}

if ( ! function_exists('format_amount')) {
    function format_amount($amount = null)
    {
        return NumberHelper::format_amount($amount);
    }
}

if ( ! function_exists('standardize_amount')) {
    function standardize_amount($amount)
    {
        return NumberHelper::standardize_amount($amount);
    }
}

if ( ! function_exists('round_tax')) {
    function round_tax($amount, $decimal_places, $strict = 0): string
    {
        return NumberHelper::round_tax($amount, $decimal_places, $strict);
    }
}

// Note: For the remaining helpers, we'll load them directly from the original files
// since they haven't been fully refactored yet. This allows for gradual migration.

// Get the path to the Modules/Core/Helpers directory
$helpers_path = __DIR__ . '/';

// List of helper files that still need to be loaded directly
$remaining_helper_files = [
    'client_helper.php',
    'country_helper.php',
    'custom_values_helper.php',
    'diacritics_helper.php',
    'dropzone_helper.php',
    'e-invoice_helper.php',
    'echo_helper.php',
    'invoice_helper.php',
    'json_error_helper.php',
    'mailer_helper.php',
    'mpdf_helper.php',
    'orphan_helper.php',
    'pager_helper.php',
    'payments_helper.php',
    'pdf_helper.php',
    'redirect_helper.php',
    'template_helper.php',
    'user_helper.php',
];

// Load remaining helper files
foreach ($remaining_helper_files as $helper_file) {
    $file_path = $helpers_path . $helper_file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}
