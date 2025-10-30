<?php

declare(strict_types=1);

namespace Modules\Core\Services;

/**
 * Legacy Bridge
 * 
 * Temporary bridge to access CodeIgniter components during the migration period.
 * This centralizes all CodeIgniter dependencies in one place, making it easier
 * to remove them once the migration is complete.
 * 
 * @deprecated This class exists only for backward compatibility and will be removed
 *             once the migration from CodeIgniter to Laravel is complete.
 */
class LegacyBridge
{
    private static $instance = null;
    private $ci = null;

    private function __construct()
    {
        if (function_exists('get_instance')) {
            $this->ci = &get_instance();
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get settings model.
     */
    public function settings()
    {
        return $this->ci->mdl_settings ?? null;
    }

    /**
     * Get language instance.
     */
    public function lang()
    {
        return $this->ci->lang ?? null;
    }

    /**
     * Get session instance.
     */
    public function session()
    {
        return $this->ci->session ?? null;
    }

    /**
     * Get config instance.
     */
    public function config()
    {
        return $this->ci->config ?? null;
    }

    /**
     * Load a helper.
     */
    public function loadHelper(string $helper): void
    {
        if ($this->ci && method_exists($this->ci, 'load')) {
            $this->ci->load->helper($helper);
        }
    }

    /**
     * Check if CodeIgniter instance is available.
     */
    public function isAvailable(): bool
    {
        return $this->ci !== null;
    }

    /**
     * Get the raw CodeIgniter instance (use sparingly).
     */
    public function getRawInstance()
    {
        return $this->ci;
    }
}
