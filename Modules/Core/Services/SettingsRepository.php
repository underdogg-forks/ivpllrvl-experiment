<?php

declare(strict_types=1);

namespace Modules\Core\Services;

/**
 * Settings Repository
 * 
 * Provides a clean interface to access application settings,
 * abstracting away the underlying CodeIgniter implementation.
 * 
 * This allows helper classes to avoid direct dependency on get_instance()
 * while maintaining backward compatibility during the migration.
 */
class SettingsRepository
{
    /**
     * Get a setting value.
     */
    public function get(string $key, $default = '')
    {
        // Temporary implementation using CodeIgniter
        // Will be replaced with Laravel-based storage later
        if (function_exists('get_instance')) {
            $CI = &get_instance();
            if (isset($CI->mdl_settings)) {
                return $CI->mdl_settings->setting($key, $default);
            }
        }
        
        return $default;
    }

    /**
     * Get gateway settings.
     */
    public function getGatewaySettings(string $gateway): array
    {
        if (function_exists('get_instance')) {
            $CI = &get_instance();
            if (isset($CI->mdl_settings)) {
                return $CI->mdl_settings->gateway_settings($gateway);
            }
        }
        
        return [];
    }

    /**
     * Get date format setting.
     */
    public function getDateFormat(): string
    {
        return $this->get('date_format', 'd/m/Y');
    }
}
