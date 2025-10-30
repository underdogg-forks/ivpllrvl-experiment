<?php

declare(strict_types=1);

namespace Modules\Core\Support;

/**
 * EchoHelper
 * 
 * Static helper class converted from procedural functions.
 */
class EchoHelper
{
    /**
     * Echo something with escaped HTML special chars.
     *
     * @param mixed $output
     *
     * @return void
     */
    public static function _htmlsc($output)
    {
        if ($output == null) {
            return '';
        }
    
        echo htmlspecialchars($output, ENT_QUOTES | ENT_IGNORE);
    }

    /**
     * Echo something with escaped HTML entities.
     *
     * @param mixed $output
     *
     * @return void
     */
    public static function _htmle($output)
    {
        if ($output == null) {
            return '';
        }
    
        echo htmlentities($output, ENT_COMPAT);
    }

    /**
     * Echo a language string with the trans helper.
     *
     * @param string      $line
     * @param string      $id
     * @param null|string $default
     */
    public static function _trans($line, $id = '', $default = null): void
    {
        echo trans($line, $id, $default);
    }

    /**
     * Echo for the auto link function with special chars handling.
     *
     * @param        $str
     * @param string $type
     * @param bool   $popup
     */
    public static function _auto_link($str, $type = 'both', $popup = false): void
    {
        echo auto_link(htmlsc($str), $type, $popup);
    }

    /**
     * Output the standard CSRF protection field.
     */
    public static function _csrf_field(): void
    {
        $CI = & get_instance();
        echo '<input type="hidden" name="' . $CI->config->item('csrf_token_name');
        echo '" value="' . $CI->security->get_csrf_hash() . '">';
    }

    /**
     * Returns the correct URL for a asset within the theme directory
     * Also appends the current version to the asset to prevent browser caching issues.
     *
     * @param string $asset
     */
    public static function _theme_asset($asset): void
    {
        $asset = IP_DEBUG ? strtr($asset, ['.min.' => '.']) : $asset;
        echo base_url() . 'assets/' . get_setting('system_theme', 'invoiceplane');
        echo '/' . $asset . '?v=' . get_setting('current_version');
    }

    /**
     * Returns the correct URL for a asset within the core directory
     * Also appends the current version to the asset to prevent browser caching issues.
     *
     * @param string $asset
     */
    public static function _core_asset($asset): void
    {
        $asset = IP_DEBUG ? strtr($asset, ['.min.' => '.']) : $asset;
        echo base_url() . 'assets/core/' . $asset . '?v=' . get_setting('current_version');
    }

}
