<?php



namespace Modules\Core\Support;

use Modules\Core\Models\Setting;

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
        $value = Setting::getValue($settingKey) ?? $default;
        return $escape ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    /**
     * Get the settings for a payment gateway.
     */
    public static function getGatewaySettings(string $gateway): array
    {
        // Get all settings related to this gateway
        $prefix = $gateway . '_';
        $allSettings = Setting::getAllSettings();

        $gatewaySettings = [];
        foreach ($allSettings as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $gatewaySettings[$key] = $value;
            }
        }

        return $gatewaySettings;
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
