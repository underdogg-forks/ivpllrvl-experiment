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
     *
     * @origin Modules/Core/Helpers/redirect_helper.php
     * @param string $fallback_url_string
     * @param bool   $redirect
     *
     * @return mixed
     */
    public static function redirect_to($fallback_url_string, $redirect = true)
    {
        $redirect_url = session('redirect_to', $fallback_url_string);

        session()->forget('redirect_to');

        if ($redirect) {
            return redirect($redirect_url);
        }

        return $redirect_url;
    }

    /**
     * Sets the current URL in the session.
     *
     * @origin Modules/Core/Helpers/redirect_helper.php
     */
    public static function redirect_to_set(): void
    {
        session(['redirect_to' => request()->path()]);
    }
}
