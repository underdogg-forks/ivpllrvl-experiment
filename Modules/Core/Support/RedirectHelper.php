<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Modules\Core\Services\LegacyBridge;

/**
 * RedirectHelper
 * 
 * Static helper class converted from procedural functions.
 */
class RedirectHelper
{
    /**
     * Redirect the user to a given URL.
     *
     * @param string $fallback_url_string
     * @param bool   $redirect
     *
     * @return mixed
     */
    public static function redirect_to($fallback_url_string, $redirect = true)
    {
        $bridge = LegacyBridge::getInstance();
    
        $redirect_url = ($bridge->session()->userdata('redirect_to')) ? $bridge->session()->userdata('redirect_to') : $fallback_url_string;
    
        $bridge->session()->unset_userdata('redirect_to');
    
        if ($redirect) {
            redirect($redirect_url);
        }
    
        return $redirect_url;
    }

    /**
     * Sets the current URL in the session.
     */
    public static function redirect_to_set(): void
    {
        $bridge = LegacyBridge::getInstance();
        $bridge->session()->set_userdata('redirect_to', $CI->uri->uri_string());
    }

}
