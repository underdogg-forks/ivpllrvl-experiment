<?php

namespace Modules\Core\Controllers;

/**
 * SettingsAjaxController.
 *
 * Handles AJAX requests for settings operations
 *
 * @legacy-file application/modules/settings/controllers/Ajax.php
 */
class SettingsAjaxController
{
    public $ajax_controller = true;

    /**
     * Generate a random cron key.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @legacy-file application/modules/settings/controllers/Ajax.php
     *
     * @legacy-function getCronKey
     */
    public function getCronKey(): \Illuminate\Http\JsonResponse
    {
        // Generate a random alphanumeric string of 16 characters
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $key        = '';

        for ($i = 0; $i < 16; $i++) {
            $key .= $characters[random_int(0, mb_strlen($characters) - 1)];
        }

        return response()->json(['key' => $key]);
    }
}
