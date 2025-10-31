<?php

/**
 * Returns all errors prepared for JSON.
 */
function json_errors(): array
{
    // Think of a better name for this function. It doesn't return
    // json itself but is called from something which will.
    $return = [];

    foreach (array_keys($_POST) as $key) {
        if (form_error($key)) {
            $return[$key] = form_error($key);
        }
    }

    return $return;
}
