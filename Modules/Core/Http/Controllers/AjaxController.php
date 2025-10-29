<?php

namespace Modules\Core\Http\Controllers;

/**
 * AjaxController
 * 
 * Handles AJAX requests for settings
 * Migrated from CodeIgniter Ajax controller
 */
class AjaxController
{
    /**
     * Generate a random cron key
     */
    public function getCronKey()
    {
        // Generate a random alphanumeric string of 16 characters
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $key = '';
        
        for ($i = 0; $i < 16; $i++) {
            $key .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return response()->json(['key' => $key]);
    }
}
