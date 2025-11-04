<?php

namespace Modules\Core\Support;

/**
 * JsonErrorHelper.
 *
 * Static helper class converted from procedural functions.
 */
class JsonErrorHelper
{
    /**
     * Returns all errors prepared for JSON.
     *
     * @origin Modules/Core/Helpers/json_error_helper.php
     */
    public static function json_errors(): array
    {
        // Think of a better name for this function. It doesn't return
        // json itself but is called from something which will.
        $return = [];

        // SECURITY FIX: Use Request facade instead of direct $_POST access
        $postData = request()->all();
        foreach (array_keys($postData) as $key) {
            if (form_error($key)) {
                $return[$key] = form_error($key);
            }
        }

        return $return;
    }
}
