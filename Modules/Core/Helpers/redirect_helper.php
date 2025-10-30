<?php

/**
 * Redirect the user to a given URL.
 *
 * @param string $fallback_url_string
 * @param bool   $redirect
 *
 * @return mixed
 */
function redirect_to($fallback_url_string, $redirect = true)
{
    $CI = & get_instance();

    $redirect_url = ($CI->session->userdata('redirect_to')) ? $CI->session->userdata('redirect_to') : $fallback_url_string;

    $CI->session->unset_userdata('redirect_to');

    if ($redirect) {
        redirect($redirect_url);
    }

    return $redirect_url;
}

/**
 * Sets the current URL in the session.
 */
function redirect_to_set(): void
{
    $CI = & get_instance();
    $CI->session->set_userdata('redirect_to', $CI->uri->uri_string());
}
