<?php

declare(strict_types=1);

namespace Modules\Core\Support;

/**
 * Settings Helper Class
 * 
 * Provides static methods for retrieving application settings.
 */
class SettingsHelper
{
    /**
     * Get a setting value.
     */
    public static function getSetting(string $settingKey, $default = '', bool $escape = false)
    {
        $CI = &get_instance();
        $value = $CI->mdl_settings->setting($settingKey, $default);

        return $escape ? htmlsc($value) : $value;
    }

    /**
     * Get the settings for a payment gateway.
     */
    public static function getGatewaySettings(string $gateway): array
    {
        $CI = &get_instance();

        return $CI->mdl_settings->gateway_settings($gateway);
    }

    /**
     * Compares the two given values and outputs selected="selected"
     * if the values match or the operation is true for the single value.
     */
    public static function checkSelect($value1, $value2 = null, string $operator = '==', bool $checked = false): void
    {
        $select = $checked ? 'checked="checked"' : 'selected="selected"';

        // Instant-validate if $value1 is a bool value
        if (is_bool($value1) && $value2 === null) {
            echo $value1 ? $select : '';
            return;
        }

        switch ($operator) {
            case '==':
                $echo_selected = $value1 == $value2;
                break;
            case '!=':
                $echo_selected = $value1 != $value2;
                break;
            case 'e':
            case '!e':
                $echo_selected = empty($value1);
                break;
            default:
                $echo_selected = (bool) $value1;
                break;
        }

        echo $echo_selected ? $select : '';
    }
}
