<?php

namespace Modules\Core\Support;

/**
 * RedirectHelper.
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
        // TODO: Migrate remaining CodeIgniter dependencies to Laravel

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
        // TODO: Migrate remaining CodeIgniter dependencies to Laravel
        $bridge->session()->set_userdata('redirect_to', $CI->uri->uri_string());
    }
}
