<?php

namespace Modules\Invoices\Models;

/**
 * Template Model
 *
 * Utility class for managing invoice and quote templates
 * Migrated from CodeIgniter Mdl_Templates
 *
 * Note: This is not a database model but a utility class for template management
 */
class Template
{
    /**
     * Get invoice templates of specified type
     *
     * @param string $type 'pdf' or 'public'
     * @return array
     */
    public static function getInvoiceTemplates($type = 'pdf')
    {
        $path = $type == 'pdf'
            ? APPPATH . '/views/invoice_templates/pdf'
            : APPPATH . '/views/invoice_templates/public';

        $templates = self::getTemplatesFromPath($path);

        return self::removeExtension($templates);
    }

    /**
     * Get quote templates of specified type
     *
     * @param string $type 'pdf' or 'public'
     * @return array
     */
    public static function getQuoteTemplates($type = 'pdf')
    {
        $path = $type == 'pdf'
            ? APPPATH . '/views/quote_templates/pdf'
            : APPPATH . '/views/quote_templates/public';

        $templates = self::getTemplatesFromPath($path);

        return self::removeExtension($templates);
    }

    /**
     * Get templates from path
     *
     * @param string $path
     * @return array
     */
    private static function getTemplatesFromPath($path)
    {
        if (!is_dir($path)) {
            return [];
        }

        $templates = [];
        $files = scandir($path);

        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $templates[] = $file;
            }
        }

        return $templates;
    }

    /**
     * Remove file extensions from template names
     *
     * @param array $files
     * @return array
     */
    private static function removeExtension(array $files)
    {
        return array_map(function($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, $files);
    }
}
