<?php
// Modules/Core/Helpers/site_helper.php

if (!function_exists('site_url')) {
    /**
     * Generate a full site URL for a given path.
     *
     * @param string|null $path
     * @return string
     */
    function site_url(?string $path = null): string
    {
        // Guess base URL from config or environment
        $baseUrl = config('app.url') ?? (env('APP_URL') ?: '/');
        $baseUrl = rtrim($baseUrl, '/');
        if ($path === null || $path === '') {
            return $baseUrl;
        }
        // Remove leading slash from path
        $path = ltrim($path, '/');
        return $baseUrl . '/' . $path;
    }
}

